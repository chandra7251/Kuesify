<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\AttemptAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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

    public function play(Request $request, int $attempt): Response
    {
        $attempt = QuizAttempt::withoutGlobalScopes()->with(['quiz.questions', 'answers'])->findOrFail($attempt);
        abort_unless(
            $attempt->organization_id === session('organization_id') && $attempt->participant_id === $request->user()->id,
            403,
        );
        abort_unless($attempt->participant_id === $request->user()->id, 403);

        return Inertia::render('AttemptPlay', [
            'attempt' => [
                'id' => $attempt->id,
                'status' => $attempt->status,
                'score' => $attempt->score,
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
                    ]),
                ],
                'answers' => $attempt->answers->map(fn (AttemptAnswer $answer) => [
                    'id' => $answer->id,
                    'question_id' => $answer->question_id,
                    'answer' => $answer->answer,
                    'is_correct' => $answer->is_correct,
                    'points_awarded' => $answer->points_awarded,
                    'feedback' => $answer->feedback,
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

        return back();
    }

    public function grade(Request $request, int $attempt, int $answer): RedirectResponse
    {
        $attempt = QuizAttempt::findOrFail($attempt);
        Gate::authorize('update', $attempt->quiz);
        $answer = $attempt->answers()->findOrFail($answer);
        $data = $request->validate(['points' => ['required', 'integer', 'min:0'], 'feedback' => ['nullable', 'string', 'max:4000']]);
        $attempt->gradeEssay($answer, $data['points'], $data['feedback'] ?? null);

        return back();
    }
}
