<?php

namespace App\Http\Controllers;

use App\Models\AttemptAnswer;
use App\Models\Category;
use App\Models\LiveSession;
use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Tag;
use App\Models\UserProgress;
use App\Models\AiGeneration;
use App\Models\Material;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkspaceController extends Controller
{
    public function questions(Request $request): InertiaResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'in:multiple_choice,true_false,fill_blank,essay'],
            'tag' => ['nullable', 'integer'],
            'category' => ['nullable', 'integer'],
        ]);
        $query = Question::query()->with(['category:id,name', 'tags:id,name'])->latest();
        $query->when($filters['search'] ?? null, fn ($query, $search) => $query->where('prompt', 'like', '%'.$search.'%'))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category_id', $category))
            ->when($filters['tag'] ?? null, fn ($query, $tag) => $query->whereHas('tags', fn ($tags) => $tags->whereKey($tag)));

        return $this->page('Question Bank', 'questions', $query->paginate(15)->withQueryString(), $filters, [
            'types' => ['multiple_choice', 'true_false', 'fill_blank', 'essay'],
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function exportQuestions(): StreamedResponse
    {
        $rows = Question::with(['category:id,name', 'tags:id,name'])->orderBy('id')->get();

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['id', 'type', 'prompt', 'options_json', 'correct_answer', 'points', 'category', 'tags']);
            foreach ($rows as $question) {
                fputcsv($output, [$question->id, $question->type, $question->prompt, json_encode($question->options), $question->correct_answer, $question->points, $question->category?->name, $question->tags->pluck('name')->join('|')]);
            }
            fclose($output);
        }, 'kuesify-question-bank.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function quizzes(): InertiaResponse
    {
        abort_unless($this->canCreate(request()), 403);

        return Inertia::render('QuizBuilder', [
            'quizzes' => Quiz::with(['category:id,name', 'questions:id,prompt,type,points'])->withCount('questions')->latest()->get(),
            'questions' => Question::with('category:id,name')->latest()->get(['id', 'category_id', 'type', 'prompt', 'points']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function live(): InertiaResponse
    {
        return Inertia::render('LiveHub', [
            'sessions' => LiveSession::with(['quiz:id,title', 'participants:id,live_session_id,alias,score,kicked_at'])->latest()->paginate(15),
            'quizzes' => Quiz::where('status', 'published')->get(['id', 'title']),
        ]);
    }

    public function attempts(Request $request): InertiaResponse
    {
        $isCreator = $this->canCreate($request);
        $query = QuizAttempt::with(['quiz:id,title', 'participant:id,name,email', 'answers.question:id,prompt,type,points'])->latest();
        if (! $isCreator) {
            $query->where('participant_id', $request->user()->id);
        }

        return Inertia::render('Attempts', [
            'attempts' => $query->paginate(15),
            'publishedQuizzes' => $isCreator ? [] : Quiz::where('status', 'published')->withCount('questions')->latest()->get(['id', 'title', 'description', 'deadline_at', 'max_attempts']),
            'gradebook' => $isCreator,
        ]);
    }

    public function exportAttempts(Request $request): StreamedResponse
    {
        abort_unless($this->canCreate($request), 403);

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['Quiz', 'Participant', 'Status', 'Score', 'Submitted at']);
            QuizAttempt::with(['quiz:id,title', 'participant:id,name,email'])->latest()->each(function (QuizAttempt $attempt) use ($output): void {
                fputcsv($output, [$attempt->quiz->title, $attempt->participant->name, $attempt->status, $attempt->score, $attempt->updated_at->toIso8601String()]);
            });
            fclose($output);
        }, 'kuesify-gradebook.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function organization(Request $request): InertiaResponse
    {
        $this->requireAdmin($request);
        $organization = $this->activeOrganization();

        return $this->page('Organisasi', 'organization', $organization->members()->wherePivot('role', '!=', 'super_admin')->select('users.id', 'users.name', 'users.email', 'organization_user.role', 'organization_user.is_active')->paginate(20), [], [
            'groups' => $organization->groups()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function reports(Request $request): InertiaResponse
    {
        abort_unless($this->canCreate($request), 403);
        $isSuperAdmin = $this->isSuperAdmin($request);

        if ($isSuperAdmin) {
            $allOrgs = Organization::withCount(['members'])->latest()->get();
            $globalAttempts = QuizAttempt::withoutGlobalScopes()->whereIn('status', ['completed', 'pending_review'])->get();
            $totalGlobal = $globalAttempts->count();
            $globalQuizzes = Quiz::withoutGlobalScopes()->count();

            return $this->page('Laporan Global', 'reports', $allOrgs->map(function ($org) {
                return [
                    'id' => $org->id,
                    'title' => $org->name,
                    'prompt' => $org->name,
                    'name' => $org->name,
                    'members_count' => $org->members_count,
                    'timezone' => $org->timezone,
                    'questions_count' => Quiz::withoutGlobalScopes()->where('organization_id', $org->id)->count(),
                    'type' => 'Tenant Terdaftar',
                ];
            }), [], [
                'completionCount' => $totalGlobal,
                'averageScore' => $totalGlobal ? round($globalAttempts->avg('score'), 2) : 0,
                'totalOrganizations' => $allOrgs->count(),
                'totalQuizzes' => $globalQuizzes,
                'isGlobal' => true,
            ]);
        }

        $quizzes = Quiz::withCount('questions')->with(['questions:id,type,prompt,points'])->latest()->get();
        $attempts = QuizAttempt::whereIn('quiz_id', $quizzes->pluck('id'))->whereIn('status', ['completed', 'pending_review'])->get();
        $total = $attempts->count();

        $allQuestions = $quizzes->flatMap->questions;
        $questionIds = $allQuestions->pluck('id')->unique()->filter();

        $answerStats = $questionIds->isNotEmpty()
            ? AttemptAnswer::whereIn('question_id', $questionIds)
                ->selectRaw('question_id, count(*) as total, sum(case when is_correct = 1 then 1 else 0 end) as correct')
                ->groupBy('question_id')
                ->get()
                ->keyBy('question_id')
            : collect();

        return $this->page('Laporan Hasil', 'reports', $quizzes, [], [
            'completionCount' => $total,
            'averageScore' => $total ? round($attempts->avg('score'), 2) : 0,
            'questions' => $allQuestions->map(function (Question $question) use ($answerStats) {
                $stat = $answerStats->get($question->id);
                $totalCount = $stat ? (int) $stat->total : 0;
                $correctCount = $stat ? (int) $stat->correct : 0;

                return [
                    'id' => $question->id,
                    'prompt' => $question->prompt,
                    'points' => $question->points,
                    'correct_rate' => $totalCount > 0 ? round(($correctCount / $totalCount) * 100, 1) : null,
                ];
            })->values(),
        ]);
    }

    public function admin(Request $request): InertiaResponse
    {
        abort_unless($this->isSuperAdmin($request), 403);

        $reverbHealth = app(\App\Services\ReverbHealthService::class)->check();

        $totalAttemptsMonth = QuizAttempt::withoutGlobalScopes()->where('created_at', '>=', now()->startOfMonth())->count();
        $aiGenerationsMonth = AiGeneration::withoutGlobalScopes()->where('created_at', '>=', now()->startOfMonth())->count();
        $aiFailures = AiGeneration::withoutGlobalScopes()->where('status', 'failed')->count();

        return $this->page('Platform Admin', 'admin', Organization::withCount('members')->latest()->paginate(20), [], [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'health' => array_merge([
                'queued_jobs' => \DB::table('jobs')->count(),
                'failed_jobs' => \DB::table('failed_jobs')->count(),
                'ai_failures' => $aiFailures,
            ], $reverbHealth),
            'maintenance' => [
                'status' => 'Sistem Normal (Aktif)',
                'jadwal_rutin' => 'Setiap Minggu, 02:00 - 04:00 WIB',
                'mode' => 'Live Production',
            ],
            'ai_config' => [
                'weekly_creator_limit' => (int) config('services.gemini.weekly_creator_quota', 10),
                'generasi_bulan_ini' => $aiGenerationsMonth,
                'ai_model' => 'Gemini 2.5 Flash',
            ],
            'audio_settings' => [
                'bgm_lobby' => 'Arcade Retro 8-bit (Default)',
                'sfx_correct' => 'Crystal Chime High',
                'sfx_wrong' => 'Muted Buzzer Low',
                'default_volume' => '70%',
            ],
            'monthly_stats' => [
                'total_organizations' => Organization::count(),
                'total_users' => \App\Models\User::count(),
                'total_attempts_month' => $totalAttemptsMonth,
            ],
        ]);
    }

    private function page(string $title, string $section, mixed $items, array $filters = [], array $summary = []): InertiaResponse
    {
        return Inertia::render('Workspace', compact('title', 'section', 'items', 'filters', 'summary'));
    }

    public function materials(Request $request): InertiaResponse
    {
        abort_unless($this->canCreate($request), 403);
        $materials = Material::with('creator:id,name')->latest()->get();
        $generations = AiGeneration::with(['material:id,original_name', 'drafts'])->latest()->get();

        $weeklyLimit = (int) config('services.gemini.weekly_creator_quota', 10);
        $weeklyUsed = AiGeneration::where('creator_id', $request->user()->id)
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();

        $quota = [
            'weekly_limit' => $weeklyLimit,
            'weekly_used' => $weeklyUsed,
            'weekly_remaining' => max(0, $weeklyLimit - $weeklyUsed),
        ];

        return Inertia::render('Materials', compact('materials', 'generations', 'quota'));
    }

    public function exportReport(Request $request): StreamedResponse
    {
        abort_unless($this->canCreate($request), 403);
        $quizzes = Quiz::withCount('questions')->latest()->get();
        $attempts = QuizAttempt::with(['quiz:id,title', 'participant:id,name,email'])->whereIn('quiz_id', $quizzes->pluck('id'))->whereIn('status', ['completed', 'pending_review'])->get();

        return response()->streamDownload(function () use ($quizzes, $attempts): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['Quiz', 'Total Soal', 'Total Attempts', 'Rata-rata Skor']);
            foreach ($quizzes as $quiz) {
                $quizAttempts = $attempts->where('quiz_id', $quiz->id);
                fputcsv($output, [$quiz->title, $quiz->questions_count, $quizAttempts->count(), $quizAttempts->count() ? round($quizAttempts->avg('score'), 2) : 0]);
            }
            fputcsv($output, []);
            fputcsv($output, ['Quiz', 'Peserta', 'Status', 'Skor', 'Dikumpulkan']);
            foreach ($attempts as $attempt) {
                fputcsv($output, [$attempt->quiz->title, $attempt->participant->name, $attempt->status, $attempt->score, $attempt->updated_at->toIso8601String()]);
            }
            fclose($output);
        }, 'kuesify-report.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function canCreate(Request $request): bool
    {
        return in_array($this->activeOrganization()->roleFor($request->user()), ['creator', 'organization_admin', 'super_admin'], true);
    }

    private function requireAdmin(Request $request): void
    {
        abort_unless(in_array($this->activeOrganization()->roleFor($request->user()), ['organization_admin', 'super_admin'], true), 403);
    }

    private function isSuperAdmin(Request $request): bool
    {
        return $this->activeOrganization()->roleFor($request->user()) === 'super_admin';
    }

    private function activeOrganization(): Organization
    {
        return Organization::findOrFail(app(TenantContext::class)->id());
    }
}

