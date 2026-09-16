<?php

namespace App\Jobs;

use App\Models\AiGeneration;
use App\Services\GeminiQuestionGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateQuestions implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 60, 300];

    public function __construct(public AiGeneration $generation)
    {
    }

    public function handle(GeminiQuestionGenerator $generator): void
    {
        $this->generation->update(['status' => 'generating']);

        try {
            $questions = $generator->generate($this->generation->material->extracted_text ?? '', $this->generation->question_count, $this->generation->difficulty, $this->generation->types);
            foreach ($questions as $question) {
                $this->generation->drafts()->create([
                    'type' => $question['type'], 'prompt' => $question['prompt'], 'options' => $question['options'] ?? null,
                    'correct_answer' => $question['correct_answer'] ?? null, 'explanation' => $question['explanation'] ?? null,
                    'points' => $question['points'] ?? 1000, 'status' => 'pending',
                ]);
            }
            $this->generation->update(['status' => 'review']);
        } catch (Throwable $exception) {
            $this->generation->update(['status' => 'failed', 'failure_reason' => $exception->getMessage()]);
            throw $exception;
        }
    }
}
