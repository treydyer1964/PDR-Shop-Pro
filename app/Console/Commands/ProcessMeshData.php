<?php

namespace App\Console\Commands;

use App\Models\MeshDailyRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessMeshData extends Command
{
    protected $signature = 'mesh:process
                            {--date= : Date to process (YYYY-MM-DD, defaults to today)}
                            {--url=  : Override GRIB2.gz URL (skip auto-detect)}
                            {--frame= : Specific frame timestamp YYYYMMDD-HHMMSS for historical}';

    protected $description = 'Download and render an MRMS MESH GRIB2 frame, building a daily max swath';

    // MRMS_Max_1440min = rolling 24-hour maximum MESH (correct daily swath product)
    // Individual MESH.latest is only an instantaneous snapshot — wrong for daily swaths
    const MRMS_BASE = 'https://mrms.ncep.noaa.gov/2D/MESH_Max_1440min/';

    // CONUS image overlay bounds [SW lat, SW lng, NE lat, NE lng]
    // MRMS MESH CONUS grid: lat 20.005–55.005, lon -129.995–(-60.005)
    const CONUS_SW_LAT =  20.005;
    const CONUS_SW_LNG = -129.995;
    const CONUS_NE_LAT =  54.995;
    const CONUS_NE_LNG = -60.005;

    public function handle(): int
    {
        // SPC convective day runs 12Z–12Z; subtract 12h to match hail tracker date
        $date = $this->option('date') ?: now()->subHours(12)->toDateString();

        // ── Resolve GRIB2 URL ─────────────────────────────────────────────────
        if ($this->option('url')) {
            $url = $this->option('url');
        } else {
            $url = $this->resolveUrl($date);
            if (!$url) {
                $this->warn("No MRMS MESH data available for {$date}.");
                return 0;
            }
        }

        // ── Storage paths ─────────────────────────────────────────────────────
        $storageDir = "mesh/{$date}";
        $pngRelPath = "{$storageDir}/max.png";                         // relative to storage/app/public
        $pngAbsPath = Storage::disk('public')->path($pngRelPath);
        $npyAbsPath = Storage::disk('public')->path("{$storageDir}/max.npy");

        // Ensure directory exists
        Storage::disk('public')->makeDirectory($storageDir);

        // ── Call Python renderer ──────────────────────────────────────────────
        $python = $this->findPython();
        $script  = base_path('scripts/mesh_render.py');

        $cmd = implode(' ', array_map('escapeshellarg', [
            $python, $script,
            '--url',         $url,
            '--output',      $pngAbsPath,
            '--accumulator', $npyAbsPath,
            '--downsample',  '4',
        ]));

        $this->line("Fetching: {$url}");
        exec("{$cmd} 2>&1", $output, $exitCode);

        $outputStr = implode("\n", $output);

        if ($exitCode !== 0) {
            $this->error("mesh_render.py failed (exit {$exitCode}):\n{$outputStr}");
            Log::error('mesh:process render failed', ['url' => $url, 'output' => $outputStr]);
            return 1;
        }

        $this->line($outputStr);

        // ── Update DB record ──────────────────────────────────────────────────
        $maxSize = $this->readMetaJson($pngAbsPath);

        $record = MeshDailyRecord::firstOrNew(['record_date' => $date]);
        $record->png_path        = $pngRelPath;
        $record->npy_path        = $npyAbsPath;
        $record->max_size_inches = $maxSize;
        $record->frame_count     = ($record->frame_count ?? 0) + 1;
        $record->last_frame_at   = now();
        $record->save();

        $this->info("MESH swath updated for {$date} — peak {$maxSize}\" hail.");
        return 0;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Resolve the GRIB2.gz URL for a given date.
     * MESH_Max_1440min has no .latest symlink — always list the directory.
     * For today: get the most recent file available (rolling 24-h max).
     * For historical: filter files matching the requested date.
     * NOAA keeps ~48 h of files; anything older won't be available.
     */
    private function resolveUrl(string $date): ?string
    {
        $html = @file_get_contents(self::MRMS_BASE);
        if ($html === false) {
            Log::warning('mesh:process could not fetch MRMS_Max_1440min directory listing');
            return null;
        }

        // Filenames: MRMS_MESH_Max_1440min_00.50_YYYYMMDD-HHMMSS.grib2.gz
        preg_match_all('/MRMS_MESH_Max_1440min_00\.50_(\d{8}-\d{6})\.grib2\.gz/', $html, $matches);

        if (empty($matches[1])) return null;

        $today    = now()->subHours(12)->toDateString();
        $datePart = str_replace('-', '', $date);

        if ($date === $today) {
            // Pick the most recent file in the directory (highest timestamp)
            $frames = $matches[1];
        } else {
            // Historical: only files matching the requested date
            $frames = array_filter($matches[1], fn($ts) => str_starts_with($ts, $datePart));
        }

        if (empty($frames)) return null;

        sort($frames);
        $lastFrame = end($frames);
        return self::MRMS_BASE . "MRMS_MESH_Max_1440min_00.50_{$lastFrame}.grib2.gz";
    }

    private function findPython(): string
    {
        foreach (['/usr/local/bin/python3.8', '/usr/bin/python3.8', 'python3.8', 'python3'] as $p) {
            if (@shell_exec("which {$p} 2>/dev/null")) return $p;
        }
        return 'python3';
    }

    /**
     * Read peak MESH value (inches) from the companion JSON written by mesh_render.py.
     * JSON path = same as PNG but with .json extension.
     */
    private function readMetaJson(string $pngAbsPath): float
    {
        $jsonPath = preg_replace('/\.png$/', '.json', $pngAbsPath);
        if (!file_exists($jsonPath)) return 0.0;
        $data = @json_decode(file_get_contents($jsonPath), true);
        return (float) ($data['max_inches'] ?? 0.0);
    }
}
