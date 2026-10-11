<?php

namespace App\Services;

use App\Models\LiveSession;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class LiveLeaderboardService
{
    public function for(LiveSession $session): array
    {
        $key = "live-leaderboard:{$session->id}";
        $cached = Cache::get($key);

        if ($cached !== null) {
            return $cached;
        }

        $remember = fn () => Cache::remember(
            $key,
            now()->addSeconds(10),
            fn () => $this->build($session),
        );

        try {
            return Cache::lock("{$key}:lock", 5)->block(2, $remember);
        } catch (LockTimeoutException) {
            return $remember();
        }
    }

    private function build(LiveSession $session): array
    {
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
    }

    public function forget(LiveSession $session): void
    {
        Cache::forget("live-leaderboard:{$session->id}");
    }
}
