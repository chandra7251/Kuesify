<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

<<<<<<< HEAD
#[Fillable(['name', 'email', 'password', 'avatar_key', 'preferences'])]
=======
#[Fillable(['name', 'email', 'password', 'avatar_key', 'locale'])]
>>>>>>> 6949a241a3ca3601330e70dcba839b79e06a64ea
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const AVATAR_KEYS = ['book', 'cap', 'globe', 'lamp', 'microscope', 'pencil', 'rocket', 'laptop'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
        ];
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)->withPivot('role', 'is_active')->withTimestamps();
    }

<<<<<<< HEAD
    public function createdQuizzes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Quiz::class, 'creator_id');
    }

    public function attempts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'participant_id');
=======
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)->withTimestamps();
    }

    public function collaborativeQuizzes(): BelongsToMany
    {
        return $this->belongsToMany(Quiz::class, 'quiz_collaborator')->withTimestamps();
>>>>>>> 6949a241a3ca3601330e70dcba839b79e06a64ea
    }
}
