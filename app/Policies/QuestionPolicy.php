<?php

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Models\Question;
use App\Models\User;
use App\Support\TenantContext;

class QuestionPolicy
{
    public function create(User $user): bool
    {
        $organizationId = app(TenantContext::class)->id();
        $role = $organizationId ? $user->organizations()->whereKey($organizationId)->value('organization_user.role') : null;

        return $role !== null && OrganizationRole::tryFrom($role)?->canCreateQuiz() === true;
    }

    public function update(User $user, Question $question): bool
    {
        $organizationId = app(TenantContext::class)->id();
        $role = $organizationId ? $user->organizations()->whereKey($organizationId)->value('organization_user.role') : null;

        return $this->create($user) && ($question->creator_id === $user->id || in_array($role, [OrganizationRole::OrganizationAdmin->value, OrganizationRole::SuperAdmin->value], true));
    }

    public function delete(User $user, Question $question): bool
    {
        return $this->update($user, $question);
    }
}
