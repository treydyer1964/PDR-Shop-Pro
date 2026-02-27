<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VinService
{
    /**
     * Decode a VIN using the free NHTSA API.
     * Returns an array of decoded fields, or null on failure.
     */
    public function decode(string $vin): ?array
    {
        $vin = strtoupper(trim($vin));

        if (!$this->isValidVin($vin)) {
            return null;
        }

        try {
            $response = Http::timeout(8)
                ->get("https://vpic.nhtsa.dot.gov/api/vehicles/decodevin/{$vin}", [
                    'format' => 'json',
                ]);

            if (!$response->ok()) {
                return null;
            }

            $results = collect($response->json('Results', []));

            $get = fn(string $variable) => $results
                ->firstWhere('Variable', $variable)['Value'] ?? null;

            // Clean up "null" strings the API returns
            $clean = function (?string $v): ?string {
                if (!$v || strtolower($v) === 'null' || $v === 'Not Applicable') return null;
                return $v;
            };

            $year  = (int) ($get('Model Year') ?? 0);
            $make  = $clean($get('Make'));
            $model = $clean($get('Model'));

            if (!$year || !$make || !$model) {
                return null;
            }

            // Build engine string from displacement + config
            $displacement = $clean($get('Displacement (L)'));
            $cylinders    = $clean($get('Engine Number of Cylinders'));
            $engine = null;
            if ($displacement) {
                $engine = number_format((float) $displacement, 1) . 'L';
                if ($cylinders) {
                    $engine .= " {$cylinders}-Cyl";
                }
            }

            return [
                'vin'        => $vin,
                'year'       => $year,
                'make'       => ucwords(strtolower($make)),
                'model'      => ucwords(strtolower($model)),
                'trim'       => $clean($get('Trim')),
                'body_style' => $clean($get('Body Class')),
                'drive_type' => $clean($get('Drive Type')),
                'engine'     => $engine,
            ];
        } catch (\Throwable $e) {
            Log::warning("NHTSA VIN decode failed for {$vin}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Apply EXIF orientation to an image so it is upright before sending to OpenAI.
     * iOS stores photos with raw sensor rotation; EXIF tag says how to display it.
     * GPT-4o-mini ignores EXIF, so we bake the rotation into the pixel data.
     */
    private function normalizeOrientation(string $base64Image): string
    {
        $binary = base64_decode($base64Image);

        // Read EXIF orientation from the raw binary
        $exif = @exif_read_data('data://image/jpeg;base64,' . $base64Image);
        $orientation = $exif['Orientation'] ?? 1;

        if ($orientation === 1) {
            return $base64Image; // Already upright
        }

        $image = @imagecreatefromstring($binary);
        if (!$image) {
            return $base64Image; // Can't decode — send as-is
        }

        switch ($orientation) {
            case 2: imageflip($image, IMG_FLIP_HORIZONTAL); break;
            case 3: $image = imagerotate($image, 180, 0); break;
            case 4: imageflip($image, IMG_FLIP_VERTICAL); break;
            case 5: $image = imagerotate($image, -90, 0); imageflip($image, IMG_FLIP_HORIZONTAL); break;
            case 6: $image = imagerotate($image, -90, 0); break;  // Rotated 90° CW (most common on iOS)
            case 7: $image = imagerotate($image,  90, 0); imageflip($image, IMG_FLIP_HORIZONTAL); break;
            case 8: $image = imagerotate($image,  90, 0); break;  // Rotated 90° CCW
        }

        ob_start();
        imagejpeg($image, null, 90);
        $rotated = ob_get_clean();
        imagedestroy($image);

        return base64_encode($rotated);
    }

    /**
     * Extract a VIN from an image using OpenAI Vision (GPT-4o-mini).
     * Returns the 17-char VIN string, or null.
     */
    public function extractFromImage(string $base64Image, string $mimeType = 'image/jpeg'): ?string
    {
        $apiKey = config('services.openai.api_key');

        if (!$apiKey) {
            Log::warning('OpenAI API key not configured — VIN image extraction unavailable');
            return null;
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(20)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'max_tokens' => 100,
                    'messages' => [[
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url'    => "data:image/jpeg;base64,{$base64Image}",
                                    'detail' => 'high',
                                ],
                            ],
                            [
                                'type' => 'text',
                                'text' => 'Find the Vehicle Identification Number (VIN) in this image. '
                                    . 'A VIN is EXACTLY 17 characters — letters A-Z (never I, O, or Q) and digits 0-9 only. '
                                    . 'Look for it labeled "VIN:" on door jamb stickers, dashboard, or window stickers. '
                                    . 'Read each character carefully left to right and count: you must have exactly 17. '
                                    . 'Do not skip or merge any characters. '
                                    . 'Respond with ONLY the 17-character VIN in uppercase, no spaces or dashes. '
                                    . 'If you cannot read a complete 17-character VIN, respond with: NONE',
                            ],
                        ],
                    ]],
                ]);

            if (!$response->ok()) {
                return null;
            }

            $text = trim($response->json('choices.0.message.content', ''));

            Log::info('OpenAI VIN response', [
                'status'   => $response->status(),
                'response' => $text,
                'image_kb' => round(strlen($base64Image) * 3 / 4 / 1024),
            ]);

            // Extract VIN from response.
            // Accept 14–18 char near-matches so a single dropped character doesn't
            // silently fail — caller checks strlen to distinguish exact vs partial.
            $text = strtoupper(trim($text));
            if (preg_match('/[A-HJ-NPR-Z0-9]{14,18}/', $text, $matches)) {
                return $matches[0];
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('OpenAI VIN extraction failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Basic VIN format validation (17 chars, no I/O/Q, valid check digit).
     */
    public function isValidVin(string $vin): bool
    {
        $vin = strtoupper(trim($vin));

        if (strlen($vin) !== 17) return false;
        if (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $vin)) return false;

        // Check digit validation (position 9)
        $transliteration = [
            'A'=>1,'B'=>2,'C'=>3,'D'=>4,'E'=>5,'F'=>6,'G'=>7,'H'=>8,
            'J'=>1,'K'=>2,'L'=>3,'M'=>4,'N'=>5,'P'=>7,'R'=>9,
            'S'=>2,'T'=>3,'U'=>4,'V'=>5,'W'=>6,'X'=>7,'Y'=>8,'Z'=>9,
        ];
        $weights = [8,7,6,5,4,3,2,10,0,9,8,7,6,5,4,3,2];

        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            $char = $vin[$i];
            $value = is_numeric($char) ? (int)$char : ($transliteration[$char] ?? 0);
            $sum += $value * $weights[$i];
        }

        $remainder = $sum % 11;
        $checkDigit = $remainder === 10 ? 'X' : (string) $remainder;

        return $vin[8] === $checkDigit;
    }
}
