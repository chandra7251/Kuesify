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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $orgId = $request->session()->get('organization_id');
        $organization = $orgId ? Organization::find($orgId) : Organization::first();

        $activeRole = (string) $request->session()->get('active_role', '');
        $isSuperAdmin = $activeRole === 'super_admin' ||
            ($organization && $organization->roleFor($user) === 'super_admin') ||
            $user->organizations()->wherePivot('role', 'super_admin')->exists();

        $progress = UserProgress::where('user_id', $user->id)->first();
        $badges = $organization ? DB::table('badge_awards')
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
            'organization' => [
                'id' => $organization?->id ?? 0,
                'name' => $organization?->name ?? 'Platform Global',
                'role' => $organization ? $organization->roleFor($user) : ($isSuperAdmin ? 'super_admin' : 'participant'),
            ],
            'isSuperAdmin' => $isSuperAdmin,
            'adminAnalytics' => $adminAnalytics,
            'stats' => [
                'quizzes' => Quiz::count(),
                'questions' => Question::count(),
                'liveSessions' => LiveSession::whereIn('status', ['lobby', 'live'])->count(),
                'attempts' => QuizAttempt::where('participant_id', $user->id)->count(),
                'xp' => $progress?->xp ?? 0,
                'streak' => $progress?->streak ?? 0,
                'level' => $progress?->level ?? 1,
            ],
            'recentQuizzes' => Quiz::latest()->take(8)->get(['id', 'title', 'status', 'updated_at']),
            'badges' => $badges,
        ]);
    }
}
