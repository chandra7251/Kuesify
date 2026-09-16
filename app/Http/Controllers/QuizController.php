<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QuizController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Quiz::class);

        $data = $request->validate(['title' => ['required', 'string', 'max:120']]);

        Quiz::create([
            'organization_id' => app(TenantContext::class)->id(),
            'creator_id' => $request->user()->id,
            'title' => $data['title'],
            'status' => 'draft',
        ]);

        return back();
    }

    public function update(Request $request, int $quiz): RedirectResponse
    {
        $quiz = Quiz::withoutGlobalScopes()->findOrFail($quiz);
        Gate::authorize('update', $quiz);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:4000'],
            'visibility' => ['nullable', 'in:private,organization,public'], 'max_attempts' => ['nullable', 'integer', 'min:1', 'max:100'],
            'deadline_at' => ['nullable', 'date'], 'show_explanations' => ['nullable', 'boolean'], 'category_id' => ['nullable', 'exists:categories,id'],
        ]);
        $quiz->update($data);

        return back();
    }

    public function syncQuestions(Request $request, int $quiz): RedirectResponse
    {
        $quiz = Quiz::withoutGlobalScopes()->findOrFail($quiz);
        Gate::authorize('update', $quiz);
        $data = $request->validate(['question_ids' => ['required', 'array', 'min:1'], 'question_ids.*' => ['integer', 'distinct']]);
        $ids = $data['question_ids'];
        abort_unless(Question::whereIn('id', $ids)->count() === count($ids), 422);
        $quiz->questions()->sync(collect($ids)->mapWithKeys(fn (int $id, int $index) => [$id => ['position' => $index + 1]]));

        return back();
    }

    public function publish(int $quiz): RedirectResponse
    {
        $quiz = Quiz::withoutGlobalScopes()->findOrFail($quiz);
        Gate::authorize('update', $quiz);
        $quiz->publish();

        return back();
    }

    public function clone(int $quiz): RedirectResponse
    {
        $quiz = Quiz::withoutGlobalScopes()->with('questions')->findOrFail($quiz);
        Gate::authorize('update', $quiz);
        $copy = $quiz->replicate(['status', 'created_at', 'updated_at']);
        $copy->title = $quiz->title.' (copy)';
        $copy->creator_id = request()->user()->id;
        $copy->status = 'draft';
        $copy->save();
        $copy->questions()->sync($quiz->questions->mapWithKeys(fn (Question $question) => [$question->id => ['position' => $question->pivot->position]]));

        return back();
    }

    public function archive(int $quiz): RedirectResponse
    {
        $quiz = Quiz::withoutGlobalScopes()->findOrFail($quiz);
        Gate::authorize('update', $quiz);
        $quiz->update(['status' => 'archived']);

        return back();
    }

    public function cover(Request $request, int $quiz): RedirectResponse
    {
        $quiz = Quiz::withoutGlobalScopes()->findOrFail($quiz);
        Gate::authorize('update', $quiz);
        $data = $request->validate(['cover' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']]);
        $quiz->update(['cover_image' => $data['cover']->store('quiz-covers/'.$quiz->organization_id, 'public')]);

        return back();
    }
}
