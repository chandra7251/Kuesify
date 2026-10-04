<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Mission;
use App\Models\UserMission;
use App\Models\QuizAttempt;
use App\Models\UserProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class GamificationService
{
    public function participantBadges(int $organizationId, int $userId): array
    {
        $definitions = $this->badgeDefinitions();
        foreach ($definitions as $definition) {
            Badge::firstOrCreate(['key' => $definition['key']], $definition);
        }

        $awards = DB::table('badge_awards')
            ->join('badges', 'badge_awards.badge_id', '=', 'badges.id')
            ->where('badge_awards.organization_id', $organizationId)
            ->where('badge_awards.user_id', $userId)
            ->get(['badges.key', 'badges.name', 'badge_awards.created_at as earned_at']);
        $awardByKey = $awards->keyBy('key');

        $progress = UserProgress::withoutGlobalScopes()
            ->where(['organization_id' => $organizationId, 'user_id' => $userId])
            ->first();
        $attempts = QuizAttempt::withoutGlobalScopes()
            ->where(['organization_id' => $organizationId, 'participant_id' => $userId])
            ->where('status', 'completed');
        $completedAttempts = (clone $attempts)->count();
        $bestScore = (int) ((clone $attempts)->max('score') ?? 0);
        $perfectScores = (clone $attempts)->where('score', 100)->count();

        $catalog = collect($definitions);
        foreach ($awards as $award) {
            if (! $catalog->contains('key', $award->key)) {
                $catalog->push([
                    'key' => $award->key,
                    'name' => $award->name,
                    'description' => 'Badge legacy dari aktivitas belajar.',
                    'rarity' => 'common',
                    'criteria_type' => 'completed_attempts',
                    'criteria_value' => 1,
                ]);
            }
        }

        return $catalog->map(function (array $badge) use ($awardByKey, $progress, $completedAttempts, $bestScore, $perfectScores): array {
            $current = match ($badge['criteria_type']) {
                'xp' => (int) ($progress?->xp ?? 0),
                'streak' => (int) ($progress?->streak ?? 0),
                'score' => $bestScore,
                'perfect_score' => $perfectScores,
                default => $completedAttempts,
            };
            $target = (int) $badge['criteria_value'];
            $award = $awardByKey->get($badge['key']);

            return [
                'key' => $badge['key'],
                'name' => $badge['name'],
                'description' => $badge['description'],
                'rarity' => $badge['rarity'],
                'criteria_value' => $target,
                'progress' => min($current, $target),
                'progress_percent' => $target > 0 ? min(100, (int) round(($current / $target) * 100)) : 0,
                'earned' => $award !== null,
                'earned_at' => $award?->earned_at,
            ];
        })->sortByDesc('earned')->values()->all();
    }

    public function badgeDefinitions(): array
    {
        return [
            ['key' => 'first_attempt', 'name' => 'Awakening of Novice', 'description' => 'Selesaikan kuis pertama untuk memulai petualangan belajarmu.', 'rarity' => 'common', 'criteria_type' => 'completed_attempts', 'criteria_value' => 1],
            ['key' => 'score_100', 'name' => 'Centurion Scholar', 'description' => 'Raih skor 100 mutlak dalam satu arena kuis.', 'rarity' => 'rare', 'criteria_type' => 'score', 'criteria_value' => 100],
            ['key' => 'streak_3', 'name' => 'Iron Will', 'description' => 'Bangun tekad baja dengan streak belajar 3 hari beruntun.', 'rarity' => 'rare', 'criteria_type' => 'streak', 'criteria_value' => 3],
            ['key' => 'quizzes_5', 'name' => 'Trial Challenger', 'description' => 'Taklukkan 5 sesi kuis latihan berbeda.', 'rarity' => 'rare', 'criteria_type' => 'completed_attempts', 'criteria_value' => 5],
            ['key' => 'streak_7', 'name' => 'Flame of Dedication', 'description' => 'Pertahankan kobaran api streak selama 7 hari tanpa henti.', 'rarity' => 'epic', 'criteria_type' => 'streak', 'criteria_value' => 7],
            ['key' => 'quizzes_15', 'name' => 'Dungeon Conqueror', 'description' => 'Selesaikan 15 sesi kuis dengan sukses.', 'rarity' => 'epic', 'criteria_type' => 'completed_attempts', 'criteria_value' => 15],
            ['key' => 'xp_1000', 'name' => 'Grand Scholar', 'description' => 'Kumpulkan akumulasi 1.000 XP dari arena pengetahuan.', 'rarity' => 'epic', 'criteria_type' => 'xp', 'criteria_value' => 1000],
            ['key' => 'perfect_score', 'name' => 'Flawless Mastery', 'description' => 'Selesaikan satu kuis dengan akurasi 100% tanpa kesalahan.', 'rarity' => 'legendary', 'criteria_type' => 'perfect_score', 'criteria_value' => 1],
            ['key' => 'streak_14', 'name' => 'Unyielding Vanguard', 'description' => 'Jaga ketangguhan belajar selama 14 hari berturut-turut.', 'rarity' => 'legendary', 'criteria_type' => 'streak', 'criteria_value' => 14],
            ['key' => 'xp_5000', 'name' => 'High Archmage', 'description' => 'Kumpulkan 5.000 XP dari berbagai kuis dan ekspedisi misi.', 'rarity' => 'legendary', 'criteria_type' => 'xp', 'criteria_value' => 5000],
            ['key' => 'streak_30', 'name' => 'Eternal Titan', 'description' => 'Legenda hidup: Selesaikan kuis selama 30 hari penuh tanpa putus.', 'rarity' => 'mythic', 'criteria_type' => 'streak', 'criteria_value' => 30],
            ['key' => 'xp_10000', 'name' => 'Sovereign of Wisdom', 'description' => 'Raih tahta tertinggi pengetahuan dengan 10.000 XP.', 'rarity' => 'mythic', 'criteria_type' => 'xp', 'criteria_value' => 10000],
        ];
    }

    public function missionDefinitions(): array
    {
        return [
            ['key' => 'daily_quiz_1', 'kind' => 'daily', 'title' => 'Daily Bounty: Ujian Fajar', 'description' => 'Selesaikan minimal satu kuis hari ini.', 'goal' => 1, 'reward_xp' => 25],
            ['key' => 'daily_score_80', 'kind' => 'daily', 'title' => 'Daily Bounty: Presisi Tempur', 'description' => 'Selesaikan kuis dengan skor minimal 80 hari ini.', 'goal' => 2, 'reward_xp' => 50],
            ['key' => 'daily_quiz_2', 'kind' => 'daily', 'title' => 'Daily Bounty: Grinding Harian', 'description' => 'Selesaikan dua sesi kuis dalam satu hari.', 'goal' => 2, 'reward_xp' => 60],
            ['key' => 'weekly_quiz_3', 'kind' => 'weekly', 'title' => 'Weekly Raid: Ritme Petualang', 'description' => 'Selesaikan tiga kuis minggu ini.', 'goal' => 3, 'reward_xp' => 75],
            ['key' => 'weekly_quiz_7', 'kind' => 'weekly', 'title' => 'Weekly Raid: Marathon Sang Juara', 'description' => 'Selesaikan tujuh sesi kuis dalam satu pekan.', 'goal' => 7, 'reward_xp' => 200],
            ['key' => 'weekly_perfect', 'kind' => 'weekly', 'title' => 'Weekly Raid: Sentuhan Midas', 'description' => 'Raih skor 100 sempurna pada kuis minggu ini.', 'goal' => 2, 'reward_xp' => 150],
            ['key' => 'learning_quiz_5', 'kind' => 'campaign', 'title' => 'Quest: Penjelajah Pustaka', 'description' => 'Selesaikan 5 kuis untuk membuka lencana Trial Challenger.', 'goal' => 5, 'reward_xp' => 150],
            ['key' => 'campaign_dungeon_15', 'kind' => 'campaign', 'title' => 'Raid: Penakluk 15 Medan', 'description' => 'Taklukkan 15 kuis untuk membuka lencana Epic Dungeon Conqueror.', 'goal' => 15, 'reward_xp' => 350],
            ['key' => 'campaign_dungeon_25', 'kind' => 'campaign', 'title' => 'Grand Raid: 25 Dungeon Takluk', 'description' => 'Taklukkan 25 kuis untuk membuka lencana Epic Dungeon Grandmaster.', 'goal' => 25, 'reward_xp' => 600],
            ['key' => 'campaign_flawless', 'kind' => 'campaign', 'title' => 'Ascension: Ketepatan Tanpa Celah', 'description' => 'Raih nilai 100 sempurna pada 2 kuis untuk membuka lencana Legendary Flawless Mastery.', 'goal' => 2, 'reward_xp' => 500],
            ['key' => 'campaign_titan_30', 'kind' => 'campaign', 'title' => 'Apex Quest: Ketekunan Titan 30 Hari', 'description' => 'Pertahankan streak 30 hari utuh untuk membuka gelar Mythic Eternal Titan.', 'goal' => 30, 'reward_xp' => 2000],
        ];
    }

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
            $previousLevel = $progress->level;
            $xp = $progress->xp + $attempt->score;
            $newLevel = $this->levelForXp($xp);
            $progress->update(['xp' => $xp, 'level' => $newLevel, 'streak' => $streak, 'last_activity_date' => $today]);
            DB::table('xp_events')->where('quiz_attempt_id', $attempt->id)->update(['level_before' => $previousLevel, 'level_after' => $newLevel]);

            $completedAttempts = QuizAttempt::withoutGlobalScopes()->where(['organization_id' => $attempt->organization_id, 'participant_id' => $attempt->participant_id, 'status' => 'completed'])->count();
            $this->award($attempt, 'first_attempt', 'Awakening of Novice');
            if ($completedAttempts >= 5) $this->award($attempt, 'quizzes_5', 'Trial Challenger');
            if ($completedAttempts >= 15) $this->award($attempt, 'quizzes_15', 'Dungeon Conqueror');
            if ($attempt->score >= 100) $this->award($attempt, 'score_100', 'Centurion Scholar');
            if ($streak >= 3) $this->award($attempt, 'streak_3', 'Iron Will');
            if ($streak >= 7) $this->award($attempt, 'streak_7', 'Flame of Dedication');
            if ($streak >= 14) $this->award($attempt, 'streak_14', 'Unyielding Vanguard');
            if ($streak >= 30) $this->award($attempt, 'streak_30', 'Eternal Titan');
            if ($xp >= 1000) $this->award($attempt, 'xp_1000', 'Grand Scholar');
            if ($xp >= 5000) $this->award($attempt, 'xp_5000', 'High Archmage');
            if ($xp >= 10000) $this->award($attempt, 'xp_10000', 'Sovereign of Wisdom');
            if ($attempt->score > 0 && $attempt->score === $attempt->quiz->questions()->sum('points')) $this->award($attempt, 'perfect_score', 'Flawless Mastery');
            $this->updateMissions($attempt);
        });
    }

private function updateMissions(QuizAttempt $attempt): void
    {
        $organization = $attempt->quiz->organization;
        $timezone = $organization?->timezone ?: 'UTC';
        $today = Carbon::now($timezone);
        $definitions = $this->missionDefinitions();

        $progressRecord = UserProgress::withoutGlobalScopes()->where([
            'organization_id' => $attempt->organization_id,
            'user_id' => $attempt->participant_id,
        ])->first();
        $streak = (int) ($progressRecord?->streak ?? 0);
        $isScore80 = $attempt->score >= 80;
        $isPerfectScore = ($attempt->score > 0 && $attempt->score === $attempt->quiz->questions()->sum('points'));

        foreach ($definitions as $definition) {
            $mission = Mission::firstOrCreate(['key' => $definition['key']], $definition);
            $periodKey = match ($mission->kind) {
                'daily' => $today->toDateString(),
                'weekly' => $today->copy()->startOfWeek()->toDateString(),
                default => 'lifetime',
            };
            $userMission = UserMission::withoutGlobalScopes()->firstOrCreate([
                'organization_id' => $attempt->organization_id,
                'user_id' => $attempt->participant_id,
                'mission_id' => $mission->id,
                'period_key' => $periodKey,
            ]);

            if ($userMission->completed_at !== null) {
                continue;
            }

            $eligible = match ($mission->key) {
                'daily_score_80' => $isScore80,
                'weekly_perfect', 'campaign_flawless' => $isPerfectScore,
                'campaign_titan_30' => true,
                default => true,
            };

            if (! $eligible) {
                continue;
            }

            $progress = match ($mission->key) {
                'campaign_titan_30' => min($mission->goal, $streak),
                default => min($mission->goal, $userMission->progress + 1),
            };

            $completedAt = $progress >= $mission->goal ? now() : null;
            $userMission->update(['progress' => $progress, 'completed_at' => $completedAt]);

            if ($completedAt !== null && $mission->reward_xp > 0) {
                $progressRecord = UserProgress::withoutGlobalScopes()->firstOrCreate(
                    ['organization_id' => $attempt->organization_id, 'user_id' => $attempt->participant_id],
                    ['xp' => 0, 'level' => 1, 'streak' => 0],
                );
                $newXp = $progressRecord->xp + $mission->reward_xp;
                $progressRecord->update(['xp' => $newXp, 'level' => $this->levelForXp($newXp)]);
            }
        }
    }

    private function levelForXp(int $xp): int
    {
        $level = 1;
        $threshold = 0;

        while ($xp >= $threshold + ($level * 1000)) {
            $threshold += $level * 1000;
            $level++;
        }

        return $level;
    }

    private function award(QuizAttempt $attempt, string $key, string $name): void
    {
        $definition = collect($this->badgeDefinitions())->firstWhere('key', $key) ?? [
            'name' => $name,
            'description' => 'Badge dari aktivitas belajar.',
            'rarity' => 'common',
            'criteria_type' => 'completed_attempts',
            'criteria_value' => 1,
        ];
        $badge = Badge::updateOrCreate(['key' => $key], $definition);
        DB::table('badge_awards')->insertOrIgnore([
            'organization_id' => $attempt->organization_id, 'user_id' => $attempt->participant_id,
            'badge_id' => $badge->id, 'quiz_attempt_id' => $attempt->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
