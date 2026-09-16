<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ai_generation_id', 'type', 'prompt', 'options', 'correct_answer', 'explanation', 'points', 'status'])]
class AiQuestionDraft extends Model
{
    protected function casts(): array
    {
        return ['options' => 'array'];
    }

    public function generation(): BelongsTo
    {
        return $this->belongsTo(AiGeneration::class, 'ai_generation_id');
    }

    public function approve(User $creator): Question
    {
        if ($this->status !== 'pending' || $creator->id !== $this->generation->creator_id) {
            throw new DomainException('AI draft cannot be approved by this user.');
        }

        $question = Question::create([
            'organization_id' => $this->generation->organization_id,
            'creator_id' => $creator->id,
            'type' => $this->type,
            'prompt' => $this->prompt,
            'options' => $this->options,
            'correct_answer' => $this->correct_answer,
            'explanation' => $this->explanation,
            'points' => $this->points,
        ]);
        $this->update(['status' => 'approved']);

        return $question;
    }
}
