<?php

namespace App\Http\Controllers;

use App\Models\LiveSession;
use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $organization = Organization::findOrFail($request->session()->get('organization_id'));
        $role = $organization->roleFor($request->user());

        // Super Admin — Executive Platform Dashboard (global, tanpa tenant scope)
        if ($role === 'super_admin') {
            return $this->superAdminDashboard($organization, $request);
        }

        // Org Admin — Tenant dashboard
        if ($role === 'organization_admin') {
            return $this->orgAdminDashboard($organization, $request);
        }

        // Fallback — creator / participant pakai Dashboard generik (legacy, akan dipecah ke creator/dashboard & participant/dashboard)
        $progress = UserProgress::where('user_id', $request->user()->id)->first();
        $badges = DB::table('badge_awards')
            ->join('badges', 'badge_awards.badge_id', '=', 'badges.id')
            ->where('badge_awards.organization_id', $organization->id)
            ->where('badge_awards.user_id', $request->user()->id)
            ->select('badges.key', 'badges.name')
            ->get()->toArray();

        return Inertia::render('Dashboard', [
            'organization' => ['id' => $organization->id, 'name' => $organization->name, 'role' => $role],
            'stats' => [
                'quizzes' => Quiz::count(),
                'questions' => Question::count(),
                'liveSessions' => LiveSession::whereIn('status', ['lobby', 'live'])->count(),
                'attempts' => QuizAttempt::where('participant_id', $request->user()->id)->count(),
                'xp' => $progress?->xp ?? 0,
                'streak' => $progress?->streak ?? 0,
                'level' => $progress?->level ?? 1,
            ],
            'recentQuizzes' => Quiz::latest()->take(8)->get(['id', 'title', 'status', 'updated_at']),
            'badges' => $badges,
        ]);
    }

    private function superAdminDashboard(Organization $organization, Request $request): Response
    {
        // Tanpa global scope — angka platform se-Indonesia
        $stats = [
            'organizations' => Organization::withoutGlobalScopes()->count(),
            'users' => DB::table('users')->count(),
            'quizzes' => Quiz::withoutGlobalScopes()->count(),
            'questions' => Question::withoutGlobalScopes()->count(),
            'attempts' => QuizAttempt::withoutGlobalScopes()->count(),
            'liveSessions' => LiveSession::withoutGlobalScopes()->whereIn('status', ['lobby', 'live'])->count(),
        ];

        // 7 hari terakhir — volume attempt & skor rata-rata
        $dailyAttempts = collect(range(6, 0))->map(function (int $offset) {
            $date = Carbon::today()->subDays($offset);
            $row = QuizAttempt::withoutGlobalScopes()
                ->whereDate('created_at', $date)
                ->selectRaw('count(*) as c, avg(score) as avg_score')
                ->first();
            return [
                'date' => $date->toDateString(),
                'count' => (int) ($row->c ?? 0),
                'avg_score' => $row->avg_score !== null ? round((float) $row->avg_score, 1) : null,
            ];
        })->values()->all();

        // Distribusi role dari pivot organization_user
        $roleCounts = DB::table('organization_user')
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($r) => ['role' => $r->role, 'count' => (int) $r->count])
            ->all();

        // Top 5 organisasi by attempts (global)
        $topOrgs = Organization::withoutGlobalScopes()
            ->withCount(['users', 'quizAttempts'])
            ->orderByDesc('quiz_attempts_count')
            ->take(5)
            ->get(['id', 'name'])
            ->map(fn (Organization $o) => [
                'id' => $o->id,
                'name' => $o->name,
                'users_count' => (int) $o->users_count,
                'attempts_count' => (int) $o->quiz_attempts_count,
            ])->all();

        $reverbHealth = app(\App\Services\ReverbHealthService::class)->check();
        $health = array_merge([
            'queued_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'ai_failures' => \App\Models\AiGeneration::withoutGlobalScopes()->where('status', 'failed')->count(),
        ], $reverbHealth);

        $recentAttempts = QuizAttempt::withoutGlobalScopes()
            ->with(['quiz:id,title', 'participant:id,name'])
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (QuizAttempt $a) => [
                'id' => $a->id,
                'quiz' => $a->quiz->title ?? '—',
                'participant' => $a->participant->name ?? '—',
                'score' => $a->score,
                'status' => $a->status,
                'created_at' => $a->created_at?->toIso8601String(),
            ])->all();

        return Inertia::render('superadmin/dashboard', [
            'organization' => ['id' => $organization->id, 'name' => $organization->name, 'role' => 'super_admin'],
            'stats' => $stats,
            'dailyAttempts' => $dailyAttempts,
            'roleCounts' => $roleCounts,
            'topOrgs' => $topOrgs,
            'reverbHealth' => $reverbHealth,
            'health' => $health,
            'recentAttempts' => $recentAttempts,
        ]);
    }

    private function orgAdminDashboard(Organization $organization, Request $request): Response
    {
        $stats = [
            'members' => $organization->members()->count(),
            'groups' => $organization->groups()->count(),
            'quizzes' => $organization->quizzes()->count(),
            'questions' => Question::count(),
            'liveSessions' => LiveSession::whereIn('status', ['lobby', 'live'])->count(),
            'attempts' => QuizAttempt::whereIn('quiz_id', $organization->quizzes()->pluck('id'))->count(),
        ];

        return Inertia::render('admin/dashboard', [
            'organization' => ['id' => $organization->id, 'name' => $organization->name, 'role' => 'organization_admin'],
            'stats' => $stats,
            'recentQuizzes' => Quiz::latest()->take(8)->get(['id', 'title', 'status', 'updated_at']),
        ]);
    }
}
