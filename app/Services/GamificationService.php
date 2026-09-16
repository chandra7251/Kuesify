<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\QuizAttempt;
use App\Models\UserProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class GamificationService
{
    public function recordAttempt(QuizAttempt $attempt): void
    {
        if ($attempt->status !== 'completed') {
            return;
        }

        DB::transaction(function () use ($attempt): void {
            $created = DB::table('xp_events')->insertOrIgnore([
                'organization_id' => $attempt->organization_id, 'user_id' => $attempt->participant_id,
                'quiz_attempt_id' => $attempt->id, 'amount' => $attempt->score, 'created_at' => now(), 'updated_at' => now(),
            ]);

            if ($created === 0) {
                return;
            }

            $progress = UserProgress::withoutGlobalScopes()->firstOrCreate(
                ['organization_id' => $attempt->organization_id, 'user_id' => $attempt->participant_id],
                ['xp' => 0, 'level' => 1, 'streak' => 0],
            );
            $org = \App\Models\Organization::withoutGlobalScopes()->findOrFail($attempt->organization_id);
            $tz = $org->timezone ?: 'UTC';
            $today = Carbon::now($tz)->toDateString();
            $yesterday = Carbon::now($tz)->subDay()->toDateString();
            $streak = $progress->last_activity_date?->toDateString() === $today
                ? $progress->streak
                : ($progress->last_activity_date?->toDateString() === $yesterday ? $progress->streak + 1 : 1);
            $xp = $progress->xp + $attempt->score;
            $progress->update(['xp' => $xp, 'level' => intdiv($xp, 1000) + 1, 'streak' => $streak, 'last_activity_date' => $today]);

            $this->award($attempt, 'first_attempt', 'First Attempt');
            if ($attempt->score >= 100) $this->award($attempt, 'score_100', 'Century Score');
            if ($streak >= 3) $this->award($attempt, 'streak_3', 'Three Day Streak');
            if ($xp >= 1000) $this->award($attempt, 'xp_1000', '1,000 XP');
            if ($attempt->score > 0 && $attempt->score === $attempt->quiz->questions()->sum('points')) $this->award($attempt, 'perfect_score', 'Perfect Score');
        });
    }

    private function award(QuizAttempt $attempt, string $key, string $name): void
    {
        $badge = Badge::firstOrCreate(['key' => $key], ['name' => $name]);
        DB::table('badge_awards')->insertOrIgnore([
            'organization_id' => $attempt->organization_id, 'user_id' => $attempt->participant_id,
            'badge_id' => $badge->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
