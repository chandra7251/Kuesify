<?php

use App\Models\Organization;
use App\Models\Quiz;
use App\Models\User;
use App\Support\TenantContext;

afterEach(fn () => app(TenantContext::class)->clear());

it('shows tenant quizzes only in active organization', function () {
    $first = Organization::factory()->create();
    $second = Organization::factory()->create();
    $creator = User::factory()->create();

    Quiz::withoutGlobalScopes()->create([
        'organization_id' => $first->id,
        'creator_id' => $creator->id,
        'title' => 'Tenant one quiz',
        'status' => 'draft',
    ]);
    Quiz::withoutGlobalScopes()->create([
        'organization_id' => $second->id,
        'creator_id' => $creator->id,
        'title' => 'Tenant two quiz',
        'status' => 'draft',
    ]);

    app(TenantContext::class)->set($first);

    expect(Quiz::pluck('title')->all())->toBe(['Tenant one quiz']);
});

it('keeps roles per organization through memberships', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();

    $organization->members()->attach($creator, ['role' => 'creator']);

    expect($organization->roleFor($creator))->toBe('creator');
});
