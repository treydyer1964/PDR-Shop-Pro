<?php

namespace App\Console\Commands;

use App\Models\MeshDailyRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessMeshData extends Command
{
    protected $signature = 'mesh:process
                            {--date= : Date to process (YYYY-MM-DD, defaults to today)}
                            {--url=  : Override GRIB2.gz URL (skip auto-detect)}
                            {--frame= : Specific frame timestamp YYYYMMDD-HHMMSS for historical}';

    protected $description = 'Download and render an MRMS MESH GRIB2 frame, building a daily max swath';

    // MRMS base URL (freely accessible, no auth required)
    // Note: /data/2D/MESH/ redirects to /2D/MESH/ — use the canonical path
    const MRMS_BASE = 'https://mrms.ncep.noaa.gov/2D/MESH/';

    // CONUS image overlay bounds [SW lat, SW lng, NE lat, NE lng]
    // MRMS MESH CONUS grid: lat 20.005–55.005, lon -129.995–(-60.005)
    const CONUS_SW_LAT =  20.005;
    const CONUS_SW_LNG = -129.995;
    const CONUS_NE_LAT =  54.995;
    const CONUS_NE_LNG = -60.005;

    public function handle(): int
    {
        $date = $this->option('date') ?: now()->toDateString();

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
            '--downsample',  '10',
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
        // Load the npy to get the actual peak value (expensive for 3500×7000 grid)
        // Instead, we read it from the rendered max.npy via a quick Python one-liner
        $maxSize = $this->readNpyMax($python, $npyAbsPath);

        MeshDailyRecord::updateOrCreate(
            ['record_date' => $date],
            [
                'png_path'        => $pngRelPath,
                'npy_path'        => $npyAbsPath,
                'max_size_inches' => $maxSize,
                'frame_count'     => \DB::raw('frame_count + 1'),
                'last_frame_at'   => now(),
            ]
        );

        $this->info("MESH swath updated for {$date} — peak {$maxSize}\" hail.");
        return 0;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Resolve the GRIB2.gz URL for a given date.
     * For today: use the `.latest.grib2.gz` symlink (always current).
     * For historical: list the NOAA directory and find the last frame.
     */
    private function resolveUrl(string $date): ?string
    {
        $today = now()->toDateString();

        if ($date === $today) {
            // NOAA publishes a "latest" symlink — always points to current frame
            return self::MRMS_BASE . 'MRMS_MESH.latest.grib2.gz';
        }

        // Historical: try to list directory and find a frame for that date
        $datePart = str_replace('-', '', $date); // 20260305
        $listUrl  = self::MRMS_BASE;

        $html = @file_get_contents($listUrl);
        if ($html === false) {
            Log::warning('mesh:process could not fetch MRMS directory listing');
            return null;
        }

        // Extract filenames matching MRMS_MESH_00.50_YYYYMMDD-HHMMSS.grib2.gz
        preg_match_all('/MRMS_MESH_00\.50_(\d{8}-\d{6})\.grib2\.gz/', $html, $matches);

        $frames = array_filter($matches[1], fn($ts) => str_starts_with($ts, $datePart));
        if (empty($frames)) {
            return null; // NOAA only keeps ~24–48 h; older data won't be available
        }

        // Use the last frame of the day (most complete swath for historical runs)
        sort($frames);
        $lastFrame = end($frames);
        return self::MRMS_BASE . "MRMS_MESH_00.50_{$lastFrame}.grib2.gz";
    }

    private function findPython(): string
    {
        foreach (['/usr/local/bin/python3.8', '/usr/bin/python3.8', 'python3.8', 'python3'] as $p) {
            if (@shell_exec("which {$p} 2>/dev/null")) return $p;
        }
        return 'python3';
    }

    /**
     * Quick Python one-liner to get the max value from a .npy file (in inches).
     */
    private function readNpyMax(string $python, string $npyPath): float
    {
        if (!file_exists($npyPath)) return 0.0;

        $val = shell_exec(
            escapeshellarg($python) . ' -c ' .
            escapeshellarg("import numpy as np; a=np.load(" . json_encode($npyPath) . "); print(round(float(a.max()),2))") .
            ' 2>/dev/null'
        );

        return (float) trim((string) $val);
    }
}
