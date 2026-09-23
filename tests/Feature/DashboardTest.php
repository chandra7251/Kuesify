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

it('renders executive dashboard with platform analytics for super admin', function () {
    $admin = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($admin, ['role' => 'super_admin']);

    $this->actingAs($admin)->withSession([
        'organization_id' => $organization->id,
        'active_role' => 'super_admin',
    ]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('isSuperAdmin', true)
            ->has('adminAnalytics.kpi.total_organizations')
            ->has('adminAnalytics.charts.attempts_trend')
            ->has('adminAnalytics.charts.role_distribution')
            ->has('adminAnalytics.charts.top_organizations')
        );
});
