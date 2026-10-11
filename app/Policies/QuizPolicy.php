<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Quiz;
use App\Models\User;
use App\Support\TenantContext;

class QuizPolicy
{
    public function create(User $user): bool
    {
        $organizationId = app(TenantContext::class)->id();
        $role = $organizationId ? $user->organizations()->whereKey($organizationId)->value('organization_user.role') : null;

        return $role !== null && OrganizationRole::tryFrom($role)?->canCreateQuiz() === true;
    }

    public function update(User $user, Quiz $quiz): bool
    {
        $organizationId = app(TenantContext::class)->id();
        $role = $organizationId ? $user->organizations()->whereKey($organizationId)->value('organization_user.role') : null;

        return $this->create($user) && ($quiz->creator_id === $user->id || $quiz->collaborators()->whereKey($user)->exists() || in_array($role, [OrganizationRole::OrganizationAdmin->value, OrganizationRole::SuperAdmin->value], true));
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        $deadline = $quiz->getRawOriginal('deadline_at');

        return $this->update($user, $quiz)
            && $quiz->status === 'published'
            && $deadline !== null
            && Carbon::createFromFormat('Y-m-d H:i:s', substr($deadline, 0, 19), 'Asia/Jakarta')->isPast();
    }
}
