<?php

namespace App\Http\Controllers;

use App\Models\AiQuestionDraft;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AiQuestionDraftController extends Controller
{
    public function approve(Request $request, int $draft): RedirectResponse
    {
        $draft = AiQuestionDraft::findOrFail($draft);
        abort_unless($draft->generation->organization_id === session('organization_id'), 404);
        $draft->approve($request->user());

        return back();
    }

    public function update(Request $request, int $draft): RedirectResponse
    {
        $draft = AiQuestionDraft::findOrFail($draft);
        abort_unless($draft->generation->organization_id === session('organization_id') && $draft->generation->creator_id === $request->user()->id && $draft->status === 'pending', 403);
        $data = $request->validate([
            'type' => ['required', 'in:multiple_choice,true_false,fill_blank,essay'], 'prompt' => ['required', 'string', 'max:4000'],
            'options' => ['nullable', 'array'], 'correct_answer' => ['nullable', 'string', 'max:4000'], 'explanation' => ['nullable', 'string', 'max:4000'],
            'points' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);
        $draft->update($data);

        return back();
    }

    public function reject(Request $request, int $draft): RedirectResponse
    {
        $draft = AiQuestionDraft::findOrFail($draft);
        abort_unless($draft->generation->organization_id === session('organization_id') && $draft->generation->creator_id === $request->user()->id && $draft->status === 'pending', 403);
        $draft->update(['status' => 'rejected']);

        return back();
    }
}
