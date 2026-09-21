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

        $typesList = implode(', ', $types);
        $prompt = <<<PROMPT
Create exactly {$count} {$difficulty} quiz questions based strictly on the following material:
{$material}

Requirements:
- Allowed question types: {$typesList}
- Language: Follow the language of the material (e.g. Indonesian if the material is in Indonesian).
- Return ONLY a JSON array containing exactly {$count} question objects.
- Each question object must have this exact structure:
  - "type": string (must be one of: {$typesList})
  - "prompt": string (the question text)
  - "options": array of strings (required for "multiple_choice", must have 4 distinct options; null or omitted for other types)
  - "correct_answer": string (the correct answer text matching one of the options for multiple_choice, or "True"/"False" for true_false, or answer text for fill_blank; null or omitted for essay)
  - "explanation": string or null (brief explanation of why the answer is correct)
PROMPT;

        try {
            $response = Http::timeout(30)->acceptJson()->post(
                'https://generativelanguage.googleapis.com/v1beta/models/'.config('services.gemini.model').':generateContent?key='.$key,
                [
                    'contents' => [
                        ['parts' => [
                            ['text' => $prompt],
                        ]],
                    ],
                    'generationConfig' => ['responseMimeType' => 'application/json'],
                ],
            )->throw()->json('candidates.0.content.parts.0.text');

            $questions = json_decode((string) $response, true, flags: JSON_THROW_ON_ERROR);
        } catch (RequestException $exception) {
            $status = $exception->response?->status();
            $message = match ($status) {
                429 => 'Gemini quota habis. Coba lagi beberapa saat.',
                503 => 'Layanan Gemini sedang sibuk. Coba lagi dalam beberapa saat.',
                default => 'Gemini tidak dapat dihubungi. Coba lagi.',
            };

            throw new RuntimeException($message, previous: $exception);
        } catch (JsonException) {
            throw new RuntimeException('Gemini mengembalikan format soal tidak valid. Coba lagi.');
        }

        if (isset($questions['questions']) && is_array($questions['questions'])) {
            $questions = $questions['questions'];
        }

        if (! is_array($questions)) {
            throw new RuntimeException('Gemini mengembalikan format soal tidak valid. Coba lagi.');
        }

        $normalized = [];
        foreach ($questions as $question) {
            if (! is_array($question)) {
                continue;
            }

            $promptText = $question['prompt'] ?? $question['question'] ?? null;
            $type = $question['type'] ?? (count($types) === 1 ? $types[0] : null);
            $options = $question['options'] ?? null;
            $correctAnswer = $question['correct_answer'] ?? $question['answer'] ?? null;

            if (is_bool($correctAnswer)) {
                $correctAnswer = $correctAnswer ? 'True' : 'False';
            } elseif (is_numeric($correctAnswer) && is_array($options) && isset($options[$correctAnswer])) {
                $correctAnswer = (string) $options[$correctAnswer];
            } elseif ($correctAnswer !== null) {
                $correctAnswer = (string) $correctAnswer;
            }

            if (is_array($options)) {
                $options = array_values(array_map('strval', $options));
            }

            $normalized[] = [
                'type' => $type,
                'prompt' => $promptText,
                'options' => $options,
                'correct_answer' => $correctAnswer,
                'explanation' => isset($question['explanation']) ? (string) $question['explanation'] : null,
            ];
        }

        $questions = $normalized;

        if (count($questions) !== $count) {
            throw new RuntimeException('Gemini mengembalikan format soal tidak valid. Coba lagi.');
        }

        foreach ($questions as $question) {
            if (! in_array($question['type'] ?? null, $types, true)
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
