<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Services\LiveLeaderboardService;

#[Fillable(['organization_id', 'quiz_id', 'host_id', 'pin', 'broadcast_token', 'status', 'lobby_locked', 'question_duration', 'speed_multiplier', 'current_question_id', 'question_started_at'])]
class LiveSession extends Model
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return ['question_started_at' => 'datetime'];
    }

    public static function open(Quiz $quiz, User $host, int $duration, int $speedMultiplier): self
    {
        if (! $quiz->isEligibleForLiveSession()) {
            throw new DomainException('Essay questions are not supported in live sessions.');
        }

        $quiz->questions()->firstOrFail();

        return static::create([
            'organization_id' => $quiz->organization_id,
            'quiz_id' => $quiz->id,
            'host_id' => $host->id,
            'pin' => (string) random_int(100000, 999999),
            'broadcast_token' => Str::uuid()->toString(),
            'status' => 'lobby',
            'question_duration' => $duration,
            'speed_multiplier' => $speedMultiplier,
            'current_question_id' => null,
            'question_started_at' => null,
        ]);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(LiveParticipant::class);
    }

    public function joinGuest(string $alias): LiveParticipant
    {
        if (! in_array($this->status, ['lobby', 'live'], true) || $this->lobby_locked) {
            throw new DomainException('Lobby is closed.');
        }

        return $this->participants()->create(['alias' => $alias, 'reconnect_token' => Str::uuid()->toString()]);
    }

    public function lockLobby(): void
    {
        $this->update(['lobby_locked' => true]);
    }

    public function start(): void
    {
        if ($this->status !== 'lobby') {
            throw new DomainException('Only a lobby can start.');
        }

        $this->update(['status' => 'live', 'current_question_id' => $this->quiz->questions()->firstOrFail()->id, 'question_started_at' => now()]);
    }

    public function nextQuestion(): void
    {
        if ($this->status !== 'live') {
            throw new DomainException('Only a live session can change question.');
        }

        $current = $this->quiz->questions()->whereKey($this->current_question_id)->firstOrFail();
        $next = $this->quiz->questions()->wherePivot('position', '>', $current->pivot->position)->first();

        $next
            ? $this->update(['current_question_id' => $next->id, 'question_started_at' => now()])
            : $this->update(['status' => 'ended', 'current_question_id' => null, 'question_started_at' => null]);
    }

    public function end(): void
    {
        if (! in_array($this->status, ['lobby', 'live'], true)) {
            throw new DomainException('Only an open session can end.');
        }

        $this->update(['status' => 'ended', 'current_question_id' => null, 'question_started_at' => null]);
    }

    public function kick(LiveParticipant $participant): void
    {
        if ($participant->live_session_id !== $this->id) {
            throw new DomainException('Participant is not in this session.');
        }

        $participant->update(['kicked_at' => now()]);
    }

    public function submit(LiveParticipant $participant, Question $question, string $answer): LiveAnswer
    {
        $liveAnswer = DB::transaction(function () use ($participant, $question, $answer): LiveAnswer {
            $session = static::withoutGlobalScopes()->lockForUpdate()->findOrFail($this->id);
            $lockedParticipant = LiveParticipant::lockForUpdate()->findOrFail($participant->id);
            if ($session->status !== 'live' || $session->current_question_id !== $question->id || $lockedParticipant->live_session_id !== $session->id || $lockedParticipant->kicked_at) {
                throw new DomainException('Question is not active.');
            }

            $remaining = (int) max(0, now()->diffInSeconds($session->question_started_at->copy()->addSeconds($session->question_duration), false));
            if ($remaining === 0) {
                throw new DomainException('Answer window has closed.');
            }

            $correct = hash_equals((string) $question->correct_answer, trim($answer));
            $points = $correct ? $question->points + ($remaining * $session->speed_multiplier) : 0;
            try {
                $created = $lockedParticipant->answers()->create(['question_id' => $question->id, 'answer' => $answer, 'is_correct' => $correct, 'points_awarded' => $points]);
            } catch (QueryException) {
                throw new DomainException('A participant may answer each question only once.');
            }
            if ($points > 0) {
                $lockedParticipant->increment('score', $points);
            }

            return $created;
        });

        app(LiveLeaderboardService::class)->forget($this);

        return $liveAnswer;
    }
}
