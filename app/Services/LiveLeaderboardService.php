<?php

namespace App\Services;

use App\Models\LiveSession;
use Illuminate\Support\Facades\Cache;

class LiveLeaderboardService
{
    public function for(LiveSession $session): array
    {
        return Cache::remember("live-leaderboard:{$session->id}", now()->addSeconds(10), function () use ($session) {
            return $session->participants()
                ->whereNull('kicked_at')
                ->withCount([
                    'answers as correct_count' => fn ($q) => $q->where('is_correct', true),
                    'answers as wrong_count' => fn ($q) => $q->where('is_correct', false),
                    'answers as total_answered',
                ])
                ->orderByDesc('score')
                ->orderBy('id')
                ->get(['id', 'alias', 'avatar_key', 'score'])
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'alias' => $p->alias,
                    'avatar_key' => $p->avatar_key,
                    'score' => $p->score,
                    'correct_count' => (int) $p->correct_count,
                    'wrong_count' => (int) $p->wrong_count,
                    'total_answered' => (int) $p->total_answered,
                ])
                ->toArray();
        });
    }

    public function forget(LiveSession $session): void
    {
        Cache::forget("live-leaderboard:{$session->id}");
    }
}
