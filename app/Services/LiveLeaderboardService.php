<?php

namespace App\Services;

use App\Models\LiveSession;
use Illuminate\Support\Facades\Cache;

class LiveLeaderboardService
{
    public function for(LiveSession $session): array
    {
        return Cache::remember("live-leaderboard:{$session->id}", now()->addSeconds(10), fn () => $session->participants()
            ->whereNull('kicked_at')->orderByDesc('score')->orderBy('id')->get(['id', 'alias', 'score'])->toArray());
    }

    public function forget(LiveSession $session): void
    {
        Cache::forget("live-leaderboard:{$session->id}");
    }
}
