<?php

namespace App\Models;

use App\Support\TenantContext;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['organization_id', 'creator_id', 'category_id', 'title', 'description', 'status', 'visibility', 'cover_image', 'deadline_at', 'max_attempts', 'show_explanations', 'settings'])]
class Quiz extends Model
{
    protected function casts(): array
    {
        return ['settings' => 'array', 'deadline_at' => 'datetime', 'show_explanations' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('organization', function (Builder $query): void {
            $organizationId = app(TenantContext::class)->id();

            $organizationId === null
                ? $query->whereRaw('1 = 0')
                : $query->where('organization_id', $organizationId);
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'quiz_question')->withPivot('position')->orderBy('quiz_question.position');
    }

    public function publish(): void
    {
        if (! $this->questions()->exists()) {
            throw new DomainException('A quiz needs at least one question before publication.');
        }

        $this->update(['status' => 'published']);
    }

    public function isEligibleForLiveSession(): bool
    {
        return ! $this->questions()->where('type', 'essay')->exists();
    }
}
