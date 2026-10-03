<?php

namespace App\Http\Controllers;

use App\Models\AttemptAnswer;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Notifications\AttemptGraded;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class QuizAttemptController extends Controller
{
    public function store(Request $request, int $quiz): RedirectResponse
    {
        $quiz = Quiz::where('status', 'published')->findOrFail($quiz);
        $attempt = QuizAttempt::start($quiz, $request->user());

        return redirect()->route('attempts.play', $attempt);
    }

    public function study(Request $request, int $quiz): JsonResponse|Response
    {
        $quiz = Quiz::with('questions')->where('status', 'published')->findOrFail($quiz);
        abort_unless($quiz->deadline_at?->isPast(), 403);
        $organizationId = session('organization_id');
        $role = $organizationId ? $request->user()->organizations()->whereKey($organizationId)->value('organization_user.role') : null;
        abort_unless($role === 'participant', 403);
        abort_unless(QuizAttempt::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where('quiz_id', $quiz->id)
            ->where('participant_id', $request->user()->id)
            ->where('status', 'completed')
            ->exists(), 403);

        $payload = [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'flashcards' => $quiz->questions->map(fn (Question $question) => [
                'id' => $question->id,
                'prompt' => $question->prompt,
                'answer' => $question->correct_answer,
                'explanation' => $question->explanation,
                'hint' => $question->hint,
            ])->values(),
        ];

        if (! $request->expectsJson()) {
            return Inertia::render('StudyMode', ['quiz' => $payload]);
        }

        return response()->json(['quiz' => $payload]);
    }

    public function play(Request $request, int $attempt): Response
    {
        $attempt = QuizAttempt::withoutGlobalScopes()->with(['quiz.questions', 'answers'])->findOrFail($attempt);
        abort_unless(
            $attempt->organization_id === session('organization_id') && $attempt->participant_id === $request->user()->id,
            403,
        );
        abort_unless($attempt->participant_id === $request->user()->id, 403);

        $attemptsUsed = QuizAttempt::withoutGlobalScopes()
            ->where('quiz_id', $attempt->quiz_id)
            ->where('participant_id', $attempt->participant_id)
            ->count();
        $completedAttempts = QuizAttempt::withoutGlobalScopes()
            ->where('quiz_id', $attempt->quiz_id)
            ->where('participant_id', $attempt->participant_id)
            ->where('status', 'completed');
        $maxAttempts = $attempt->quiz->max_attempts;
        $totalPoints = (int) $attempt->quiz->questions()->sum('points');
        $correctAnswers = $attempt->answers->where('is_correct', true)->count();
        $incorrectAnswers = $attempt->answers->where('is_correct', false)->count();
        $answeredCount = $attempt->answers->count();
        $unansweredCount = max(0, $attempt->quiz->questions->count() - $answeredCount);
        $percentage = $totalPoints > 0 && $attempt->score !== null ? round(($attempt->score / $totalPoints) * 100, 1) : null;
        $xpEvent = DB::table('xp_events')->where('quiz_attempt_id', $attempt->id)->first(['amount', 'level_before', 'level_after']);
        $xpEarned = (int) ($xpEvent?->amount ?? 0);
        $earnedBadges = DB::table('badge_awards')
            ->join('badges', 'badge_awards.badge_id', '=', 'badges.id')
            ->where('badge_awards.quiz_attempt_id', $attempt->id)
            ->orderBy('badge_awards.created_at')
            ->get(['badges.key', 'badges.name']);

        return Inertia::render('AttemptPlay', [
            'attempt' => [
                'id' => $attempt->id,
                'status' => $attempt->status,
                'score' => $attempt->score,
                'attempts_used' => $attemptsUsed,
                'attempts_remaining' => $maxAttempts === null ? null : max(0, $maxAttempts - $attemptsUsed),
                'retry_allowed' => $attempt->quiz->allow_retry && ($maxAttempts === null || $attemptsUsed < $maxAttempts),
                'latest_score' => $completedAttempts->clone()->latest('id')->value('score'),
                'best_score' => $completedAttempts->max('score'),
                'result' => [
                    'total_points' => $totalPoints,
                    'percentage' => $percentage,
                    'correct_answers' => $attempt->status === 'in_progress' ? null : $correctAnswers,
                    'incorrect_answers' => $attempt->status === 'in_progress' ? null : $incorrectAnswers,
                    'unanswered_answers' => $attempt->status === 'in_progress' ? null : $unansweredCount,
                    'answered_answers' => $attempt->status === 'in_progress' ? null : $answeredCount,
                    'xp_earned' => $xpEarned,
                    'level_before' => $xpEvent?->level_before,
                    'level_after' => $xpEvent?->level_after,
                    'level_up' => $xpEvent?->level_before !== null && $xpEvent?->level_after > $xpEvent?->level_before,
                    'earned_badges' => $earnedBadges->map(fn ($badge) => ['key' => $badge->key, 'name' => $badge->name])->values(),
                ],
                'quiz' => [
                    'id' => $attempt->quiz->id,
                    'title' => $attempt->quiz->title,
                    'show_explanations' => $attempt->quiz->show_explanations,
                    'questions' => $attempt->quiz->questions->map(fn (Question $question) => [
                        'id' => $question->id,
                        'type' => $question->type,
                        'prompt' => $question->prompt,
                        'options' => $question->options,
                        'points' => $question->points,
                        'explanation' => $question->explanation,
                        'hint' => $question->hint,
                    ]),
                ],
                'answers' => $attempt->answers->map(fn (AttemptAnswer $answer) => [
                    'id' => $answer->id,
                    'question_id' => $answer->question_id,
                    'answer' => $answer->answer,
                    'is_correct' => $attempt->status === 'in_progress' ? null : $answer->is_correct,
                    'points_awarded' => $attempt->status === 'in_progress' ? null : $answer->points_awarded,
                    'feedback' => $attempt->status === 'in_progress' ? null : $answer->feedback,
                ]),
            ],
        ]);
    }

    public function upsertAnswer(Request $request, int $attempt, int $question): RedirectResponse
    {
        $attempt = QuizAttempt::findOrFail($attempt);
        abort_unless($attempt->participant_id === $request->user()->id && $attempt->status === 'in_progress', 403);
        $question = Question::findOrFail($question);
        abort_unless($attempt->quiz->questions()->whereKey($question)->exists(), 422);
        $data = $request->validate(['answer' => ['required', 'string', 'max:4000']]);
        $attempt->answer($question, $data['answer']);

        return redirect()->route('attempts.play', $attempt);
    }

    public function submit(Request $request, int $attempt): RedirectResponse
    {
        $attempt = QuizAttempt::findOrFail($attempt);
        abort_unless($attempt->participant_id === $request->user()->id && $attempt->status === 'in_progress', 403);
        $attempt->submit();

        if ($attempt->status === 'pending_review') {
            $creator = $attempt->quiz->creator;
            if ($creator) {
                $creator->notify(new \App\Notifications\EssaySubmittedForReview($attempt));
            }
        }

        return back();
    }

    public function grade(Request $request, int $attempt, int $answer): RedirectResponse
    {
        $attempt = QuizAttempt::findOrFail($attempt);
        Gate::authorize('update', $attempt->quiz);
        $answer = $attempt->answers()->findOrFail($answer);
        $data = $request->validate(['points' => ['required', 'integer', 'min:0'], 'feedback' => ['nullable', 'string', 'max:4000']]);
        $attempt->gradeEssay($answer, $data['points'], $data['feedback'] ?? null);
        $attempt->loadMissing(['participant', 'quiz']);
        $attempt->participant->notify(new AttemptGraded($attempt));

        return back();
    }
}
