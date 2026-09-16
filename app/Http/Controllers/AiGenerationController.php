<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\GenerateQuestions;
use App\Models\AiGeneration;
use App\Models\Material;
use App\Models\Quiz;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AiGenerationController extends Controller
{
    public function store(Request $request, int $material): RedirectResponse
    {
        Gate::authorize('create', Quiz::class);
        $material = Material::findOrFail($material);
        abort_unless($material->status === 'extracted', 422);
        abort_if(AiGeneration::where('created_at', '>=', now()->startOfMonth())->count() >= config('services.gemini.monthly_generation_quota'), 429);
        $data = $request->validate([
            'question_count' => ['required', Rule::in([5, 10, 20])],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
            'types' => ['required', 'array', 'min:1'],
            'types.*' => [Rule::in(['multiple_choice', 'true_false', 'fill_blank', 'essay'])],
        ]);
        $generation = AiGeneration::create([
            'organization_id' => app(TenantContext::class)->id(), 'material_id' => $material->id,
            'creator_id' => $request->user()->id, 'status' => 'queued', ...$data,
        ]);
        GenerateQuestions::dispatch($generation);

        return back();
    }

    public function retry(Request $request, int $generation): RedirectResponse
    {
        $generation = AiGeneration::findOrFail($generation);
        abort_unless($generation->creator_id === $request->user()->id, 403);
        abort_unless($generation->status === 'failed', 422);

        $generation->drafts()->delete();
        $generation->update(['status' => 'queued', 'failure_reason' => null]);
        GenerateQuestions::dispatch($generation);

        return back();
    }
}
