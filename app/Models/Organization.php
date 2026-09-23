<?php

namespace App\Models;

use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'timezone'])]
class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory;

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role', 'is_active')->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->members();
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function roleFor(User $user): ?string
    {
        return $this->members()->whereKey($user)->value('organization_user.role');
    }
}
