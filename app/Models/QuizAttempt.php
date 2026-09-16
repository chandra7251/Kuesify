<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use DomainException;
use App\Services\GamificationService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'quiz_id', 'participant_id', 'status', 'score'])]
class QuizAttempt extends Model
{
    use BelongsToTenant;

    public static function start(Quiz $quiz, User $participant): self
    {
        if ($quiz->deadline_at?->isPast()) {
            throw new DomainException('The homework deadline has passed.');
        }

        if ($quiz->max_attempts !== null && static::where('quiz_id', $quiz->id)->where('participant_id', $participant->id)->count() >= $quiz->max_attempts) {
            throw new DomainException('Maximum attempts reached.');
        }

        return static::create(['organization_id' => $quiz->organization_id, 'quiz_id' => $quiz->id, 'participant_id' => $participant->id, 'status' => 'in_progress']);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_id');
    }

    public function answer(Question $question, string $answer): AttemptAnswer
    {
        $correct = $question->type !== 'essay' && hash_equals((string) $question->correct_answer, trim($answer));

        return $this->answers()->updateOrCreate(['question_id' => $question->id], ['answer' => $answer, 'is_correct' => $correct, 'points_awarded' => $correct ? $question->points : 0]);
    }

    public function submit(): void
    {
        $hasEssay = $this->answers()->whereHas('question', fn ($query) => $query->where('type', 'essay'))->exists();
        $this->update(['status' => $hasEssay ? 'pending_review' : 'completed', 'score' => $this->answers()->sum('points_awarded')]);
        app(GamificationService::class)->recordAttempt($this);
    }

    public function gradeEssay(AttemptAnswer $answer, int $points, ?string $feedback = null): void
    {
        if ($answer->quiz_attempt_id !== $this->id || $answer->question->type !== 'essay' || $points < 0 || $points > $answer->question->points) {
            throw new DomainException('Invalid essay grade.');
        }

        $answer->update(['points_awarded' => $points, 'feedback' => $feedback]);

        $ungradedEssay = $this->answers()->whereHas('question', fn ($query) => $query->where('type', 'essay'))->whereNull('feedback')->exists();
        $this->update(['status' => $ungradedEssay ? 'pending_review' : 'completed', 'score' => $this->answers()->sum('points_awarded')]);
        app(GamificationService::class)->recordAttempt($this);
    }
}
