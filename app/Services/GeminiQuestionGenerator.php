<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use JsonException;
use RuntimeException;

class GeminiQuestionGenerator
{
    public function generate(string $material, int $count, string $difficulty, array $types): array
    {
        $key = config('services.gemini.key');

        if (! $key) {
            throw new RuntimeException('GEMINI_API_KEY is not configured.');
        }

        try {
            $response = Http::timeout(30)->acceptJson()->post(
                'https://generativelanguage.googleapis.com/v1beta/models/'.config('services.gemini.model').':generateContent?key='.$key,
                [
                    'contents' => [
                        ['parts' => [
                            ['text' => "Create {$count} {$difficulty} quiz questions using only this material:\n{$material}"],
                        ]],
                    ],
                    'generationConfig' => ['responseMimeType' => 'application/json'],
                ],
            )->throw()->json('candidates.0.content.parts.0.text');

            $questions = json_decode((string) $response, true, flags: JSON_THROW_ON_ERROR);
        } catch (RequestException $exception) {
            $message = $exception->response?->status() === 429
                ? 'Gemini quota habis. Coba lagi beberapa saat.'
                : 'Gemini tidak dapat dihubungi. Coba lagi.';

            throw new RuntimeException($message, previous: $exception);
        } catch (JsonException) {
            throw new RuntimeException('Gemini mengembalikan format soal tidak valid. Coba lagi.');
        }

        if (! is_array($questions) || count($questions) !== $count) {
            throw new RuntimeException('Gemini mengembalikan format soal tidak valid. Coba lagi.');
        }

        foreach ($questions as $question) {
            if (! is_array($question)
                || ! in_array($question['type'] ?? null, $types, true)
                || ! in_array($question['type'], ['multiple_choice', 'true_false', 'fill_blank', 'essay'], true)
                || ! is_string($question['prompt'] ?? null)
                || mb_strlen($question['prompt']) > 4000
                || ($question['type'] === 'multiple_choice' && (! is_array($question['options'] ?? null) || count($question['options']) < 2))
                || ($question['type'] !== 'essay' && ! is_string($question['correct_answer'] ?? null))) {
                throw new RuntimeException('Gemini mengembalikan format soal tidak valid. Coba lagi.');
            }
        }

        return $questions;
    }
}
