<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'material_id', 'creator_id', 'status', 'question_count', 'difficulty', 'types', 'failure_reason'])]
class AiGeneration extends Model
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return ['types' => 'array'];
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(AiQuestionDraft::class);
    }

    public function material(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Material::class)->withoutGlobalScope('organization');
    }
}
