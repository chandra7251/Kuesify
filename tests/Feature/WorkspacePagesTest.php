<?php

use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Support\TenantContext;

afterEach(fn () => app(TenantContext::class)->clear());

it('shows a tenant-scoped question bank and exports only active organization questions', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    app(TenantContext::class)->set($organization);
    Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'true_false', 'prompt' => 'Visible', 'correct_answer' => 'true']);
    app(TenantContext::class)->clear();

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->get(route('questions.index'))->assertOk()->assertInertia(fn ($page) => $page->component('Workspace')->has('items.data', 1));
    $this->get(route('questions.export'))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

it('lets creator open reports but blocks participant and super admin opens platform admin', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    $superAdmin = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $organization->members()->attach($participant, ['role' => 'participant']);
    $organization->members()->attach($superAdmin, ['role' => 'super_admin']);
    app(TenantContext::class)->set($organization);
    Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Laporan', 'status' => 'published']);
    app(TenantContext::class)->clear();

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->get(route('reports.index'))->assertOk();

    $this->actingAs($participant)->post(route('organizations.switch', $organization));
    $this->get(route('reports.index'))->assertForbidden();

    $this->actingAs($superAdmin)->post(route('organizations.switch', $organization));
    $this->get(route('platform.admin'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Workspace')
            ->has('summary.health.reverb_status')
            ->has('summary.health.reverb_host')
        );
});

