<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GeneratePwaIcons extends Command
{
    protected $signature = 'pwa:icons';
    protected $description = 'Generate PWA app icons (192x192 and 512x512)';

    public function handle(): int
    {
        $outputDir = public_path('icons');

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        foreach ([192, 512] as $size) {
            $this->makeIcon($size, $outputDir . "/icon-{$size}.png");
            $this->info("Created icon-{$size}.png");
        }

        $this->info('PWA icons generated successfully.');
        return 0;
    }

    private function makeIcon(int $size, string $path): void
    {
        $img = imagecreatetruecolor($size, $size);

        // Background: slate-900 (#0f172a)
        $bg = imagecolorallocate($img, 15, 23, 42);
        imagefill($img, 0, 0, $bg);

        // Rounded corners via corner masking
        $this->roundCorners($img, $size, $bg);

        // Blue circle accent (#2563eb) — centered, 60% of icon size
        $circleColor = imagecolorallocate($img, 37, 99, 235);
        $r = (int) ($size * 0.30);
        $cx = (int) ($size / 2);
        $cy = (int) ($size / 2);
        imagefilledellipse($img, $cx, $cy, $r * 2, $r * 2, $circleColor);

        // White car icon — drawn with filled shapes (simplified)
        $white = imagecolorallocate($img, 255, 255, 255);
        $this->drawCarIcon($img, $size, $white);

        imagepng($img, $path, 9);
        imagedestroy($img);
    }

    private function roundCorners($img, int $size, int $bg): void
    {
        // We clear corners using anti-aliased arcs
        $radius = (int) ($size * 0.15);
        $transparent = imagecolorallocate($img, 0, 0, 0);

        // Top-left
        $this->fillCorner($img, 0, 0, $radius, $bg);
        // Top-right
        $this->fillCorner($img, $size - $radius * 2, 0, $radius, $bg);
        // Bottom-left
        $this->fillCorner($img, 0, $size - $radius * 2, $radius, $bg);
        // Bottom-right
        $this->fillCorner($img, $size - $radius * 2, $size - $radius * 2, $radius, $bg);
    }

    private function fillCorner($img, int $x, int $y, int $radius, int $bg): void
    {
        // Draw a filled square over the corner, then punch out the rounded portion
        // by painting the arc area with background color
        $cx = $x + $radius;
        $cy = $y + $radius;

        for ($i = $x; $i < $x + $radius; $i++) {
            for ($j = $y; $j < $y + $radius; $j++) {
                $dist = sqrt(($i - $cx) ** 2 + ($j - $cy) ** 2);
                if ($dist > $radius) {
                    imagesetpixel($img, $i, $j, $bg);
                }
            }
        }
    }

    private function drawCarIcon($img, int $size, int $color): void
    {
        // Scale factor relative to a 512px canvas
        $s = $size / 512;

        // Car body (trapezoid-ish via polygon)
        $body = [
            (int)(120 * $s), (int)(290 * $s),
            (int)(160 * $s), (int)(240 * $s),
            (int)(210 * $s), (int)(215 * $s),
            (int)(300 * $s), (int)(215 * $s),
            (int)(350 * $s), (int)(240 * $s),
            (int)(390 * $s), (int)(290 * $s),
        ];
        imagefilledpolygon($img, $body, $color);

        // Underbody
        $under = [
            (int)(110 * $s), (int)(290 * $s),
            (int)(400 * $s), (int)(290 * $s),
            (int)(400 * $s), (int)(320 * $s),
            (int)(110 * $s), (int)(320 * $s),
        ];
        imagefilledpolygon($img, $under, $color);

        // Left wheel
        imagefilledellipse($img, (int)(165 * $s), (int)(325 * $s), (int)(55 * $s), (int)(55 * $s), $color);
        // Right wheel
        imagefilledellipse($img, (int)(345 * $s), (int)(325 * $s), (int)(55 * $s), (int)(55 * $s), $color);

        // Wheel holes (erase with blue circle color — slate-900 at icon bg)
        $hole = imagecolorat($img, 0, 0); // bg color
        imagefilledellipse($img, (int)(165 * $s), (int)(325 * $s), (int)(28 * $s), (int)(28 * $s), $hole);
        imagefilledellipse($img, (int)(345 * $s), (int)(325 * $s), (int)(28 * $s), (int)(28 * $s), $hole);
    }
}
