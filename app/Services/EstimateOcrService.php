<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EstimateOcrService
{
    /**
     * Extract structured data from an insurance estimate (PDF or image).
     * Returns a normalized array of fields.
     */
    public function extract(string $filePath, string $mimeType): array
    {
        if (str_contains($mimeType, 'pdf')) {
            return $this->extractFromPdf($filePath);
        }

        return $this->extractFromImage($filePath, $mimeType);
    }

    // ── PDF path ───────────────────────────────────────────────────────────────

    private function extractFromPdf(string $filePath): array
    {
        $escaped = escapeshellarg($filePath);
        $text    = shell_exec("pdftotext {$escaped} - 2>/dev/null");

        if ($text && strlen(trim($text)) > 30) {
            return $this->parseWithText(trim($text));
        }

        throw new \RuntimeException(
            'Could not extract text from this PDF. Try uploading a clear photo of the estimate instead.'
        );
    }

    // ── Image path (Vision API) ────────────────────────────────────────────────

    private function extractFromImage(string $filePath, string $mimeType): array
    {
        $apiKey = config('services.openai.api_key');
        if (! $apiKey) {
            throw new \RuntimeException('OpenAI API key not configured.');
        }

        $base64 = base64_encode(file_get_contents($filePath));

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'      => 'gpt-4o-mini',
                'max_tokens' => 600,
                'messages'   => [[
                    'role'    => 'user',
                    'content' => [
                        [
                            'type'      => 'image_url',
                            'image_url' => [
                                'url'    => "data:{$mimeType};base64,{$base64}",
                                'detail' => 'high',
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => $this->prompt(),
                        ],
                    ],
                ]],
            ]);

        return $this->parseResponse($response);
    }

    // ── Text path (GPT-4o text completion) ────────────────────────────────────

    private function parseWithText(string $text): array
    {
        $apiKey = config('services.openai.api_key');
        if (! $apiKey) {
            throw new \RuntimeException('OpenAI API key not configured.');
        }

        // Keep within token limits
        $truncated = mb_substr($text, 0, 8000);

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'      => 'gpt-4o-mini',
                'max_tokens' => 600,
                'messages'   => [
                    [
                        'role'    => 'system',
                        'content' => 'You extract structured data from insurance auto repair estimate text. Respond only with valid JSON — no markdown, no explanation.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $this->prompt() . "\n\nEstimate text:\n" . $truncated,
                    ],
                ],
            ]);

        return $this->parseResponse($response);
    }

    // ── Response parsing ──────────────────────────────────────────────────────

    private function parseResponse($response): array
    {
        if (! $response->ok()) {
            Log::warning('EstimateOCR: OpenAI error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('OCR request failed — please try again.');
        }

        $content = trim($response->json('choices.0.message.content', ''));

        // Strip markdown code fences if model includes them
        $content = preg_replace('/^```json\s*/i', '', $content);
        $content = preg_replace('/^```\s*/i',     '', $content);
        $content = preg_replace('/\s*```$/i',     '', $content);

        $data = json_decode($content, true);

        if (! is_array($data)) {
            Log::warning('EstimateOCR: JSON parse failed', ['content' => $content]);
            throw new \RuntimeException('Could not read the estimate. Try a clearer photo or a different file.');
        }

        return $this->normalize($data);
    }

    private function normalize(array $data): array
    {
        $clean = fn ($v) => (is_string($v) && trim($v) !== '' && strtolower(trim($v)) !== 'null' && trim($v) !== 'N/A')
            ? trim($v)
            : null;

        $suppRaw = $data['supplement_number'] ?? null;
        $suppNum = is_numeric($suppRaw) ? (int) $suppRaw : null;

        return [
            'customer_first_name' => $clean($data['customer_first_name'] ?? null),
            'customer_last_name'  => $clean($data['customer_last_name']  ?? null),
            'customer_address'    => $clean($data['customer_address']    ?? null),
            'customer_city'       => $clean($data['customer_city']       ?? null),
            'customer_state'      => $clean($data['customer_state']      ?? null),
            'customer_zip'        => $clean($data['customer_zip']        ?? null),
            'customer_phone'      => $clean($data['customer_phone']      ?? null),
            'customer_email'      => $clean($data['customer_email']      ?? null),
            'vehicle_vin'         => $clean($data['vehicle_vin']         ?? null),
            'vehicle_year'        => $clean($data['vehicle_year']        ?? null),
            'vehicle_make'        => $clean($data['vehicle_make']        ?? null),
            'vehicle_model'       => $clean($data['vehicle_model']       ?? null),
            'vehicle_color'       => $clean($data['vehicle_color']       ?? null),
            'vehicle_odometer'    => $clean($data['vehicle_odometer']    ?? null),
            'insurance_company'   => $clean($data['insurance_company']   ?? null),
            'claim_number'        => $clean($data['claim_number']        ?? null),
            'policy_number'       => $clean($data['policy_number']       ?? null),
            'adjuster_name'       => $clean($data['adjuster_name']       ?? null),
            'adjuster_phone'      => $clean($data['adjuster_phone']      ?? null),
            'adjuster_email'      => $clean($data['adjuster_email']      ?? null),
            'supplement_number'   => $suppNum,
        ];
    }

    // ── Prompt ────────────────────────────────────────────────────────────────

    private function prompt(): string
    {
        return <<<'PROMPT'
Extract data from this insurance auto repair estimate. Respond with ONLY valid JSON — no markdown, no extra text:

{
  "customer_first_name": null,
  "customer_last_name": null,
  "customer_address": null,
  "customer_city": null,
  "customer_state": null,
  "customer_zip": null,
  "customer_phone": null,
  "customer_email": null,
  "vehicle_vin": null,
  "vehicle_year": null,
  "vehicle_make": null,
  "vehicle_model": null,
  "vehicle_color": null,
  "vehicle_odometer": null,
  "insurance_company": null,
  "claim_number": null,
  "policy_number": null,
  "adjuster_name": null,
  "adjuster_phone": null,
  "adjuster_email": null,
  "supplement_number": null
}

Rules:
- Use null for any field not found in the document.
- supplement_number: integer (1, 2, 3…) if this is a supplement estimate; null if it is an original estimate.
- vehicle_year: 4-digit string (e.g. "2019").
- vehicle_vin: 17-character VIN if present, otherwise null.
- Do not include dollar amounts or estimate totals.
PROMPT;
    }
}
