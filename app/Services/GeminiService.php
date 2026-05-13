<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Exceptions\GeminiException;

class GeminiService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;
    private int $maxRetries;

    public function __construct()
    {
        $this->apiKey    = config('services.gemini.key');
        $this->baseUrl   = 'https://generativelanguage.googleapis.com/v1beta/models';
        $this->model     = 'gemini-1.5-pro';
        $this->maxRetries = 3;
    }

    /**
     * Generate content from Gemini with exponential backoff on 429.
     */
    public function generate(string $prompt, array $options = []): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
                $response = Http::withHeaders(['Content-Type' => 'application/json'])
                    ->timeout(60)
                    ->post("{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}", [
                        'contents' => [
                            ['parts' => [['text' => $prompt]]]
                        ],
                        'generationConfig' => [
                            'temperature'     => $options['temperature'] ?? 0.7,
                            'maxOutputTokens' => $options['maxTokens'] ?? 4096,
                            'responseMimeType' => 'application/json',
                        ],
                        'safetySettings' => $this->safetySettings(),
                    ]);

                if ($response->status() === 429) {
                    $waitSeconds = pow(2, $attempt) * 2; // 2s, 4s, 8s
                    Log::warning("Gemini 429 rate limit. Retry {$attempt} in {$waitSeconds}s");
                    sleep($waitSeconds);
                    $attempt++;
                    continue;
                }

                if ($response->failed()) {
                    Log::error('Gemini API error', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                    throw new GeminiException("Gemini API returned HTTP {$response->status()}");
                }

                $content = $this->extractText($response->json());
                return $this->parseJsonSafe($content);

            } catch (GeminiException $e) {
                throw $e;
            } catch (\Throwable $e) {
                $lastException = $e;
                Log::error('Gemini unexpected error', ['message' => $e->getMessage()]);
                $attempt++;
                sleep(pow(2, $attempt));
            }
        }

        throw new GeminiException(
            "Gemini unreachable after {$this->maxRetries} attempts: " . ($lastException?->getMessage() ?? 'unknown'),
            0,
            $lastException
        );
    }

    /**
     * Extract text from Gemini response envelope.
     */
    private function extractText(array $response): string
    {
        return $response['candidates'][0]['content']['parts'][0]['text'] ?? throw new GeminiException('Empty Gemini response');
    }

    /**
     * Safely parse JSON from LLM output (handles ```json fences).
     */
    public function parseJsonSafe(string $raw): array
    {
        // Strip markdown code fences if present
        $clean = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $clean = preg_replace('/\s*```$/m', '', $clean);
        $clean = trim($clean);

        $decoded = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON parse error from Gemini', ['raw' => substr($raw, 0, 500)]);
            throw new GeminiException('Invalid JSON from Gemini: ' . json_last_error_msg());
        }

        return $decoded;
    }

    private function safetySettings(): array
    {
        return [
            ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
        ];
    }
}
