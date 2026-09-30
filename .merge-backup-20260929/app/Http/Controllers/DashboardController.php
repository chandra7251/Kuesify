<?php

namespace App\Http\Controllers;

use App\Models\AiGeneration;
use App\Models\LiveSession;
use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\UserProgress;
use App\Services\ReverbHealthService;
<<<<<<< HEAD
use Carbon\Carbon;
use Illuminate\Http\Request;
=======
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
>>>>>>> 6949a241a3ca3601330e70dcba839b79e06a64ea
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
<<<<<<< HEAD
        $user = $request->user();
        $orgId = $request->session()->get('organization_id');
        $organization = $orgId ? Organization::find($orgId) : Organization::first();

        $activeRole = (string) $request->session()->get('active_role', '');
        $isSuperAdmin = $activeRole === 'super_admin' ||
            ($organization && $organization->roleFor($user) === 'super_admin') ||
            $user->organizations()->wherePivot('role', 'super_admin')->exists();

        $progress = UserProgress::where('user_id', $user->id)->first();
        $badges = $organization ? DB::table('badge_awards')
=======
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

        if ($role === 'participant') {
            return $this->participantDashboard($organization, $request);
        }

        if ($role === 'creator') {
            return $this->creatorDashboard($organization, $request);
        }

        // Fallback untuk role tidak dikenal tetap memakai Dashboard legacy.
        $progress = UserProgress::where('user_id', $request->user()->id)->first();
        $badges = DB::table('badge_awards')
>>>>>>> 6949a241a3ca3601330e70dcba839b79e06a64ea
            ->join('badges', 'badge_awards.badge_id', '=', 'badges.id')
            ->where('badge_awards.organization_id', $organization->id)
            ->where('badge_awards.user_id', $user->id)
            ->select('badges.key', 'badges.name')
            ->get()->toArray() : [];

        $adminAnalytics = null;
        if ($isSuperAdmin) {
            // Trend 7 Hari Terakhir
            $dates = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->format('Y-m-d'));
            $attemptsPerDay = QuizAttempt::withoutGlobalScopes()
                ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total, AVG(score) as avg_score')
                ->groupBy('date')
                ->get()
                ->keyBy('date');

            $attemptTrendLabels = [];
            $attemptTrendData = [];
            $scoreTrendData = [];

            foreach ($dates as $d) {
                $carbon = Carbon::parse($d);
                $attemptTrendLabels[] = $carbon->format('d M');
                $item = $attemptsPerDay->get($d);
                $attemptTrendData[] = $item ? (int) $item->total : 0;
                $scoreTrendData[] = $item ? round((float) $item->avg_score, 1) : 0;
            }

            // Distribusi Role Pengguna
            $roleCounts = DB::table('organization_user')
                ->select('role', DB::raw('count(distinct user_id) as total'))
                ->groupBy('role')
                ->pluck('total', 'role');

            $roleDistribution = [
                'labels' => ['Siswa / Peserta', 'Guru / Kreator', 'Admin Sekolah', 'Super Admin'],
                'data' => [
                    (int) ($roleCounts['participant'] ?? User::doesntHave('organizations')->count()),
                    (int) ($roleCounts['creator'] ?? 0),
                    (int) ($roleCounts['organization_admin'] ?? 0),
                    (int) ($roleCounts['super_admin'] ?? 1),
                ],
            ];

            // Top 5 Organisasi / Sekolah
            $topOrgs = Organization::withCount([
                'members as users_count',
                'quizAttempts as quiz_attempts_count' => fn ($query) => $query->withoutGlobalScopes(),
            ])
                ->orderByDesc('quiz_attempts_count')
                ->take(5)
                ->get(['id', 'name', 'slug'])
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'name' => $o->name,
                    'code' => $o->slug,
                    'users_count' => $o->users_count,
                    'attempts_count' => $o->quiz_attempts_count,
                ]);

            // Utilisasi AI Bulanan
            $aiTotal = AiGeneration::count();
            $aiThisMonth = AiGeneration::where('created_at', '>=', now()->startOfMonth())->count();
            $reverbHealth = app(ReverbHealthService::class)->check();
            $reverbRunning = ($reverbHealth['reverb_status'] ?? 'offline') === 'online';

            $adminAnalytics = [
                'kpi' => [
                    'total_organizations' => Organization::count(),
                    'total_users' => User::count(),
                    'total_quizzes' => Quiz::withoutGlobalScopes()->count(),
                    'total_attempts' => QuizAttempt::withoutGlobalScopes()->count(),
                    'avg_platform_score' => round((float) QuizAttempt::withoutGlobalScopes()->avg('score') ?: 0, 1),
                    'active_live_sessions' => LiveSession::whereIn('status', ['lobby', 'live'])->count(),
                    'ai_generations_total' => $aiTotal,
                    'ai_generations_month' => $aiThisMonth,
                    'queued_jobs' => DB::table('jobs')->count(),
                    'failed_jobs' => DB::table('failed_jobs')->count(),
                    'reverb_running' => $reverbRunning,
                    'reverb_latency_ms' => $reverbHealth['reverb_latency_ms'] ?? 12,
                ],
                'charts' => [
                    'attempts_trend' => [
                        'labels' => $attemptTrendLabels,
                        'attempts' => $attemptTrendData,
                        'scores' => $scoreTrendData,
                    ],
                    'role_distribution' => $roleDistribution,
                    'top_organizations' => [
                        'labels' => $topOrgs->pluck('name')->toArray(),
                        'attempts' => $topOrgs->pluck('attempts_count')->toArray(),
                        'members' => $topOrgs->pluck('users_count')->toArray(),
                        'items' => $topOrgs->toArray(),
                    ],
                ],
                'recent_attempts' => QuizAttempt::withoutGlobalScopes()
                    ->with([
                        'quiz' => fn ($q) => $q->withoutGlobalScopes()->select('id', 'title'),
                        'participant:id,name,email',
                    ])
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn ($att) => [
                        'id' => $att->id,
                        'quiz_title' => $att->quiz?->title ?? 'Kuis Umum',
                        'participant_name' => $att->participant?->name ?? 'Peserta',
                        'organization_name' => Organization::find($att->organization_id)?->name ?? '-',
                        'score' => $att->score,
                        'status' => $att->status,
                        'created_at' => $att->created_at->diffForHumans(),
                    ]),
            ];
        }

        return Inertia::render('Dashboard', [
<<<<<<< HEAD
            'organization' => [
                'id' => $organization?->id ?? 0,
                'name' => $organization?->name ?? 'Platform Global',
                'role' => $organization ? $organization->roleFor($user) : ($isSuperAdmin ? 'super_admin' : 'participant'),
            ],
            'isSuperAdmin' => $isSuperAdmin,
            'adminAnalytics' => $adminAnalytics,
=======
            'organization' => ['id' => $organization->id, 'name' => $organization->name, 'role' => $role],
>>>>>>> 6949a241a3ca3601330e70dcba839b79e06a64ea
            'stats' => [
                'quizzes' => Quiz::count(),
                'questions' => Question::count(),
                'liveSessions' => LiveSession::whereIn('status', ['lobby', 'live'])->count(),
<<<<<<< HEAD
                'attempts' => QuizAttempt::where('participant_id', $user->id)->count(),
=======
                'attempts' => QuizAttempt::where('participant_id', $request->user()->id)->count(),
>>>>>>> 6949a241a3ca3601330e70dcba839b79e06a64ea
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

        $reverbHealth = app(ReverbHealthService::class)->check();
        $health = array_merge([
            'queued_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'ai_failures' => AiGeneration::withoutGlobalScopes()->where('status', 'failed')->count(),
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

        $classAttempts = QuizAttempt::whereIn('quiz_id', $organization->quizzes()->pluck('id'))
            ->where('status', 'completed');

        return Inertia::render('admin/dashboard', [
            'organization' => ['id' => $organization->id, 'name' => $organization->name, 'role' => 'organization_admin'],
            'stats' => $stats,
            'classPerformance' => [
                'completed' => (clone $classAttempts)->count(),
                'averageScore' => round((float) ((clone $classAttempts)->avg('score') ?? 0), 1),
            ],
            'recentQuizzes' => Quiz::latest()->take(8)->get(['id', 'title', 'status', 'updated_at']),
        ]);
    }

    private function participantDashboard(Organization $organization, Request $request): Response
    {
        $user = $request->user();
        $progress = UserProgress::where('user_id', $user->id)->first();

        $badges = DB::table('badge_awards')
            ->join('badges', 'badge_awards.badge_id', '=', 'badges.id')
            ->where('badge_awards.organization_id', $organization->id)
            ->where('badge_awards.user_id', $user->id)
            ->select('badges.key', 'badges.name')
            ->orderBy('badges.name')
            ->get()
            ->map(fn ($badge) => ['key' => $badge->key, 'name' => $badge->name])
            ->all();

        $recentAttempts = QuizAttempt::with('quiz:id,title')
            ->where('participant_id', $user->id)
            ->latest()
            ->take(5)
            ->get(['id', 'quiz_id', 'status', 'score', 'updated_at'])
            ->map(fn (QuizAttempt $attempt) => [
                'id' => $attempt->id,
                'quiz_title' => $attempt->quiz->title ?? 'Kuis',
                'status' => $attempt->status,
                'score' => $attempt->score,
                'updated_at' => $attempt->updated_at?->toIso8601String(),
            ])
            ->all();

        $availableQuizzes = Quiz::withCount('questions')
            ->where('status', 'published')
            ->latest()
            ->take(6)
            ->get(['id', 'title', 'description', 'deadline_at', 'max_attempts'])
            ->map(fn (Quiz $quiz) => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'description' => $quiz->description,
                'questions_count' => $quiz->questions_count,
                'deadline_at' => $quiz->deadline_at?->toIso8601String(),
                'max_attempts' => $quiz->max_attempts,
            ])
            ->all();

        $streakDates = DB::table('xp_events')
            ->where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->whereDate('created_at', '>=', Carbon::today()->subDays(29))
            ->selectRaw('date(created_at) as activity_date, count(*) as total')
            ->groupBy('activity_date')
            ->pluck('total', 'activity_date');

        $streakHeatmap = collect(range(29, 0))->map(function (int $offset) use ($streakDates) {
            $date = Carbon::today()->subDays($offset)->toDateString();

            return [
                'date' => $date,
                'count' => (int) ($streakDates[$date] ?? 0),
            ];
        })->values()->all();

        return Inertia::render('participant/dashboard', [
            'organization' => ['id' => $organization->id, 'name' => $organization->name, 'role' => 'participant'],
            'stats' => [
                'availableQuizzes' => Quiz::where('status', 'published')->count(),
                'attempts' => QuizAttempt::where('participant_id', $user->id)->count(),
                'completedAttempts' => QuizAttempt::where('participant_id', $user->id)->where('status', 'completed')->count(),
                'xp' => $progress?->xp ?? 0,
                'streak' => $progress?->streak ?? 0,
                'level' => $progress?->level ?? 1,
                'badges' => count($badges),
            ],
            'badges' => $badges,
            'recentAttempts' => $recentAttempts,
            'availableQuizzes' => $availableQuizzes,
            'quizOfTheDay' => $availableQuizzes[0] ?? null,
            'streakHeatmap' => $streakHeatmap,
        ]);
    }

    private function creatorDashboard(Organization $organization, Request $request): Response
    {
        $user = $request->user();
        $quizIds = Quiz::where('creator_id', $user->id)->pluck('id');
        $attempts = QuizAttempt::whereIn('quiz_id', $quizIds);
        $attemptParticipants = (clone $attempts)->distinct('participant_id')->count('participant_id');
        $completedParticipants = (clone $attempts)->where('status', 'completed')->distinct('participant_id')->count('participant_id');

        $weakTopics = DB::table('attempt_answers')
            ->join('questions', 'attempt_answers.question_id', '=', 'questions.id')
            ->join('quiz_attempts', 'attempt_answers.quiz_attempt_id', '=', 'quiz_attempts.id')
            ->whereIn('quiz_attempts.quiz_id', $quizIds)
            ->selectRaw('questions.id, questions.prompt, count(*) as total, sum(case when attempt_answers.is_correct = 1 then 1 else 0 end) as correct')
            ->groupBy('questions.id', 'questions.prompt')
            ->orderByRaw('(sum(case when attempt_answers.is_correct = 1 then 1 else 0 end) * 1.0 / count(*)) asc')
            ->take(5)
            ->get()
            ->map(function ($row) {
                $total = (int) $row->total;
                $correct = (int) $row->correct;

                return [
                    'id' => (int) $row->id,
                    'prompt' => (string) $row->prompt,
                    'total' => $total,
                    'correct_rate' => $total > 0 ? round(($correct / $total) * 100, 1) : null,
                ];
            })
            ->all();

        $answerDurations = DB::table('attempt_answers')
            ->join('quiz_attempts', 'attempt_answers.quiz_attempt_id', '=', 'quiz_attempts.id')
            ->whereIn('quiz_attempts.quiz_id', $quizIds)
            ->whereNotNull('attempt_answers.created_at')
            ->whereNotNull('quiz_attempts.created_at')
            ->get(['attempt_answers.created_at as answered_at', 'quiz_attempts.created_at as started_at'])
            ->map(function ($row) {
                $startedAt = Carbon::parse($row->started_at);
                $answeredAt = Carbon::parse($row->answered_at);

                return max(0, $startedAt->diffInSeconds($answeredAt, false));
            });

        $recentActivity = QuizAttempt::with(['quiz:id,title', 'participant:id,name'])
            ->whereIn('quiz_id', $quizIds)
            ->latest()
            ->take(6)
            ->get(['id', 'quiz_id', 'participant_id', 'status', 'score', 'updated_at'])
            ->map(fn (QuizAttempt $attempt) => [
                'id' => $attempt->id,
                'quiz_title' => $attempt->quiz->title ?? 'Kuis',
                'participant_name' => $attempt->participant->name ?? 'Peserta',
                'status' => $attempt->status,
                'score' => $attempt->score,
                'updated_at' => $attempt->updated_at?->toIso8601String(),
            ])
            ->all();

        return Inertia::render('creator/dashboard', [
            'organization' => ['id' => $organization->id, 'name' => $organization->name, 'role' => 'creator'],
            'stats' => [
                'quizzes' => $quizIds->count(),
                'questions' => Question::where('creator_id', $user->id)->count(),
                'liveSessions' => LiveSession::where('host_id', $user->id)->whereIn('status', ['lobby', 'live'])->count(),
                'attempts' => (clone $attempts)->count(),
            ],
            'analytics' => [
                'studentRetention' => $attemptParticipants > 0 ? round(($completedParticipants / $attemptParticipants) * 100, 1) : null,
                'weakTopics' => $weakTopics,
                'avgTimePerQuestion' => $answerDurations->isNotEmpty() ? round($answerDurations->avg(), 1) : null,
            ],
            'recentQuizzes' => Quiz::withCount('questions')
                ->where('creator_id', $user->id)
                ->latest()
                ->take(6)
                ->get(['id', 'title', 'status', 'updated_at'])
                ->map(fn (Quiz $quiz) => [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'status' => $quiz->status,
                    'questions_count' => $quiz->questions_count,
                    'updated_at' => $quiz->updated_at?->toIso8601String(),
                ])
                ->all(),
            'recentActivity' => $recentActivity,
        ]);
    }
}
