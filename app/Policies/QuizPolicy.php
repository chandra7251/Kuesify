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

        return $this->create($user) && ($quiz->creator_id === $user->id || in_array($role, [OrganizationRole::OrganizationAdmin->value, OrganizationRole::SuperAdmin->value], true));
    }
}
