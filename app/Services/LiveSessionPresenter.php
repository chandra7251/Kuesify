<?php

namespace App\Services;

use App\Models\LiveSession;
use App\Models\Question;
use App\Models\Quiz;

class LiveSessionPresenter
{
    public function present(LiveSession $session): array
    {
        $session = LiveSession::withoutGlobalScopes()->with('participants:id,live_session_id,alias,score,kicked_at')->findOrFail($session->id);
        $quiz = Quiz::withoutGlobalScopes()->findOrFail($session->quiz_id);
        $question = $session->current_question_id ? Question::withoutGlobalScopes()->find($session->current_question_id) : null;

        return [
            'id' => $session->id,
            'title' => $quiz->title,
            'status' => $session->status,
            'pin' => $session->pin,
            'broadcastToken' => $session->broadcast_token,
            'lobbyLocked' => $session->lobby_locked,
            'questionDuration' => $session->question_duration,
            'questionStartedAt' => $session->question_started_at?->toIso8601String(),
            'question' => $question ? ['id' => $question->id, 'type' => $question->type, 'prompt' => $question->prompt, 'options' => $question->options] : null,
            'participants' => $session->participants->whereNull('kicked_at')->values()->map(fn ($participant) => ['id' => $participant->id, 'alias' => $participant->alias, 'score' => $participant->score]),
            'leaderboard' => app(LiveLeaderboardService::class)->for($session),
        ];
    }
}
