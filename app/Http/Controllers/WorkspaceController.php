<?php

namespace App\Http\Controllers;

use App\Models\AiGeneration;
use App\Models\AttemptAnswer;
use App\Models\Category;
use App\Models\LiveSession;
use App\Models\Material;
use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Tag;
use App\Services\ReverbHealthService;
use App\Support\SimpleXlsx;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkspaceController extends Controller
{
    public function creatorQuestionBank(Request $request): InertiaResponse
    {
        abort_unless($this->canCreate($request), 403);

        return Inertia::render('creator/question-bank', [
            'questions' => Question::latest()->get(['id', 'type', 'prompt', 'hint', 'points']),
        ]);
    }

    public function questions(Request $request): InertiaResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'in:multiple_choice,true_false,fill_blank,essay'],
            'tag' => ['nullable', 'integer'],
            'category' => ['nullable', 'integer'],
        ]);
        $query = Question::query()->with(['category:id,name,theme_key', 'tags:id,name'])->latest();
        $query->when($filters['search'] ?? null, fn ($query, $search) => $query->where('prompt', 'like', '%'.$search.'%'))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category_id', $category))
            ->when($filters['tag'] ?? null, fn ($query, $tag) => $query->whereHas('tags', fn ($tags) => $tags->whereKey($tag)));

        return $this->page('Question Bank', 'questions', $query->paginate(15)->withQueryString(), $filters, [
            'types' => ['multiple_choice', 'true_false', 'fill_blank', 'essay'],
            'categories' => Category::orderBy('name')->get(['id', 'name', 'theme_key']),
            'tags' => Tag::orderBy('name')->get(['id', 'name', 'theme_key']),
        ]);
    }

    public function exportQuestions(Request $request): StreamedResponse
    {
        $rows = Question::with(['category:id,name,theme_key', 'tags:id,name'])->orderBy('id')->get();
        $exportRows = $rows->map(fn (Question $question): array => [$question->id, $question->type, $question->prompt, json_encode($question->options), $question->correct_answer, $question->points, $question->category?->name, $question->tags->pluck('name')->join('|')]);

        return $this->exportRows($request, 'kuesify-question-bank', ['id', 'type', 'prompt', 'options_json', 'correct_answer', 'points', 'category', 'tags'], $exportRows);
    }

    public function quizzes(Request $request): InertiaResponse
    {
        abort_unless($this->canCreate($request), 403);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:draft,pending_moderation,published,rejected,archived'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $quizzes = Quiz::with(['category:id,name,theme_key', 'questions:id,prompt,type,points'])
            ->withCount('questions')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('title', 'like', '%'.$search.'%'))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category_id', $category))
            ->latest()
            ->get();

        return Inertia::render('QuizBuilder', [
            'quizzes' => $quizzes,
            'questions' => Question::with('category:id,name,theme_key')->latest()->get(['id', 'category_id', 'type', 'prompt', 'points']),
            'categories' => Category::orderBy('name')->get(['id', 'name', 'theme_key']),
            'members' => $this->activeOrganization()->members()->select('users.id', 'users.name', 'organization_user.role')->wherePivot('is_active', true)->get(),
            'filters' => $filters,
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

        $rows = QuizAttempt::with(['quiz:id,title', 'participant:id,name,email'])->latest()->get()
            ->map(fn (QuizAttempt $attempt): array => [$attempt->quiz->title, $attempt->participant->name, $attempt->status, $attempt->score, $attempt->updated_at->toIso8601String()]);

        return $this->exportRows($request, 'kuesify-gradebook', ['Quiz', 'Participant', 'Status', 'Score', 'Submitted at'], $rows);
    }

    public function organization(Request $request): InertiaResponse
    {
        $this->requireAdmin($request);
        $organization = $this->activeOrganization();

        return $this->page('Organisasi', 'organization', $organization->members()->select('users.id', 'users.name', 'users.email', 'organization_user.role', 'organization_user.is_active')->paginate(20), [], [
            'groups' => $organization->groups()->orderBy('name')->get(['id', 'name', 'theme_key']),
        ]);
    }

    public function platformSection(Request $request, string $section): InertiaResponse
    {
        abort_unless($this->isSuperAdmin($request), 403);
        abort_unless(in_array($section, ['tenants', 'categories', 'ai-monitoring'], true), 404);

        return Inertia::render('superadmin/'.$section, [
            'organizations' => Organization::withCount('members')->latest()->paginate(20),
            'categories' => Category::orderBy('name')->get(['id', 'name', 'theme_key']),
            'health' => [
                'queued_jobs' => \DB::table('jobs')->count(),
                'failed_jobs' => \DB::table('failed_jobs')->count(),
                'ai_failures' => AiGeneration::withoutGlobalScopes()->where('status', 'failed')->count(),
            ],
        ]);
    }

    public function organizationAdmin(Request $request, string $section): InertiaResponse
    {
        $this->requireAdmin($request);
        abort_unless(in_array($section, ['members', 'groups', 'settings'], true), 404);
        $organization = $this->activeOrganization();

        return Inertia::render('admin/'.$section, [
            'members' => $organization->members()->select('users.id', 'users.name', 'users.email', 'organization_user.role', 'organization_user.is_active')->get(),
            'groups' => $organization->groups()->orderBy('name')->get(['id', 'name', 'theme_key']),
            'organization' => ['id' => $organization->id, 'name' => $organization->name],
        ]);
    }

    public function reports(Request $request): InertiaResponse
    {
        abort_unless($this->canCreate($request), 403);
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

        return $this->page('Laporan', 'reports', $quizzes, [], [
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

        $reverbHealth = app(ReverbHealthService::class)->check();

        // v2.3 — render halaman terpisah superadmin/platform.vue (legacy Workspace section admin pensiun)
        return Inertia::render('superadmin/platform', [
            'title' => 'Platform Admin',
            'section' => 'admin',
            'items' => Organization::withCount('members')->latest()->paginate(20),
            'filters' => [],
            'summary' => [
                'categories' => Category::orderBy('name')->get(['id', 'name', 'theme_key']),
                'health' => array_merge([
                    'queued_jobs' => \DB::table('jobs')->count(),
                    'failed_jobs' => \DB::table('failed_jobs')->count(),
                    'ai_failures' => AiGeneration::withoutGlobalScopes()->where('status', 'failed')->count(),
                ], $reverbHealth),
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

        $rows = collect([['Quiz', 'Total Soal', 'Total Attempts', 'Rata-rata Skor']]);
        foreach ($quizzes as $quiz) {
            $quizAttempts = $attempts->where('quiz_id', $quiz->id);
            $rows->push([$quiz->title, $quiz->questions_count, $quizAttempts->count(), $quizAttempts->count() ? round($quizAttempts->avg('score'), 2) : 0]);
        }
        $rows->push([], ['Quiz', 'Peserta', 'Status', 'Skor', 'Dikumpulkan']);
        foreach ($attempts as $attempt) {
            $rows->push([$attempt->quiz->title, $attempt->participant->name, $attempt->status, $attempt->score, $attempt->updated_at->toIso8601String()]);
        }

        return $this->exportRows($request, 'kuesify-report', [], $rows);
    }

    /**
     * @param  array<int, string>  $header
     * @param  Collection<int, array<int, mixed>>  $rows
     */
    private function exportRows(Request $request, string $filename, array $header, Collection $rows): StreamedResponse
    {
        $format = $request->validate(['format' => ['nullable', 'in:csv,xlsx']])['format'] ?? 'csv';
        $data = $header === [] ? $rows : $rows->prepend($header);

        if ($format === 'xlsx') {
            return response()->streamDownload(fn () => print SimpleXlsx::make($data->all()), $filename.'.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return response()->streamDownload(function () use ($data): void {
            $output = fopen('php://output', 'wb');
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
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
