<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionRequest;
use App\Models\Question;
use App\Models\Tag;
use App\Models\Quiz;
use App\Services\QuestionImportService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

class QuestionController extends Controller
{
    public function store(StoreQuestionRequest $request): RedirectResponse
    {
        Gate::authorize('create', Question::class);

        $data = $request->validated();

        DB::transaction(function () use ($data, $request): void {
            $question = Question::create([
                'organization_id' => app(TenantContext::class)->id(),
                'creator_id' => $request->user()->id,
                'type' => $data['type'],
                'prompt' => $data['prompt'],
                'options' => $data['options'] ?? null,
                'correct_answer' => $data['correct_answer'] ?? null,
                'points' => $data['points'] ?? 1000,
            ]);

            $tagIds = collect($data['tags'] ?? [])
                ->map(fn (string $name) => Tag::firstOrCreate(['organization_id' => $question->organization_id, 'name' => trim($name)])->id);

            $question->tags()->sync($tagIds);
        });

        return back();
    }

    public function update(StoreQuestionRequest $request, int $question): RedirectResponse
    {
        $question = Question::findOrFail($question);
        Gate::authorize('update', $question);
        $data = $request->validated();

        $question->update([
            'type' => $data['type'], 'prompt' => $data['prompt'], 'options' => $data['options'] ?? null,
            'correct_answer' => $data['correct_answer'] ?? null, 'points' => $data['points'] ?? $question->points,
        ]);
        $tagIds = collect($data['tags'] ?? [])->map(fn (string $name) => Tag::firstOrCreate(['organization_id' => $question->organization_id, 'name' => trim($name)])->id);
        $question->tags()->sync($tagIds);

        return back();
    }

    public function destroy(int $question): RedirectResponse
    {
        $question = Question::findOrFail($question);
        Gate::authorize('delete', $question);
        $question->delete();

        return back();
    }

    public function previewImport(\Illuminate\Http\Request $request, QuestionImportService $import): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('create', Question::class);
        $data = $request->validate(['file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:5120'], 'mapping' => ['nullable', 'array'], 'mapping.*' => ['string', 'in:type,prompt,options,correct_answer,points,tags']]);
        $result = $import->preview($data['file'], $data['mapping'] ?? []);

        return response()->json(['valid_rows' => count($result['rows']), 'invalid_rows' => count($result['errors']), 'rows' => $result['rows'], 'errors' => $result['errors']]);
    }

    public function import(\Illuminate\Http\Request $request, QuestionImportService $import): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('create', Question::class);
        $data = $request->validate(['file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:5120'], 'mapping' => ['nullable', 'array'], 'mapping.*' => ['string', 'in:type,prompt,options,correct_answer,points,tags']]);
        try {
            $count = $import->import($data['file'], $request->user(), app(TenantContext::class)->id(), $data['mapping'] ?? []);
        } catch (RuntimeException $exception) {
            $payload = json_decode($exception->getMessage(), true);
            if (is_array($payload)) {
                return response()->json($payload, 422);
            }
            throw $exception;
        }

        return response()->json(['imported' => $count]);
    }
}
