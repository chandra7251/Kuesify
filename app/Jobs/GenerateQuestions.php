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
        $organization = \App\Models\Organization::find($this->generation->organization_id);
        if ($organization) {
            app(\App\Support\TenantContext::class)->set($organization);
        }

        $this->generation->update(['status' => 'generating']);

        try {
            $materialText = $this->generation->material?->extracted_text ?? '';
            $questions = $generator->generate(
                $materialText,
                $this->generation->question_count,
                $this->generation->difficulty,
                $this->generation->types
            );

            foreach ($questions as $question) {
                $this->generation->drafts()->create([
                    'type' => $question['type'],
                    'prompt' => $question['prompt'],
                    'options' => $question['options'] ?? null,
                    'correct_answer' => $question['correct_answer'] ?? null,
                    'explanation' => $question['explanation'] ?? null,
                    'points' => $question['points'] ?? 1000,
                    'status' => 'pending',
                ]);
            }

            $this->generation->update(['status' => 'review', 'failure_reason' => null]);
        } catch (Throwable $exception) {
            $this->generation->update(['status' => 'failed', 'failure_reason' => $exception->getMessage()]);
            throw $exception;
        } finally {
            app(\App\Support\TenantContext::class)->clear();
        }
    }
}
