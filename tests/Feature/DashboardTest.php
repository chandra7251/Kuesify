<?php

use App\Models\Organization;
use App\Models\Quiz;
use App\Models\User;
use App\Support\TenantContext;
use Inertia\Testing\AssertableInertia as Assert;

it('renders active organization dashboard with tenant stats', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($user, ['role' => 'creator']);
    app(TenantContext::class)->set($organization);
    Quiz::create(['organization_id' => $organization->id, 'creator_id' => $user->id, 'title' => 'Stat', 'status' => 'draft']);
    app(TenantContext::class)->clear();

    $this->actingAs($user)->post(route('organizations.switch', $organization));
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Dashboard')->where('organization.name', $organization->name)->where('stats.quizzes', 1));
});
