<?php

use App\Models\AiGeneration;
use App\Models\Material;
use App\Models\Organization;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Bus;

afterEach(fn () => app(TenantContext::class)->clear());

it('allows creator to generate questions when within weekly quota', function () {
    Bus::fake();
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    app(TenantContext::class)->set($organization);

    $material = Material::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'disk' => 'local',
        'path' => 'doc.pdf',
        'original_name' => 'doc.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
        'status' => 'extracted',
    ]);
    app(TenantContext::class)->clear();

    $this->post(route('ai-generations.store', $material), [
        'question_count' => 5,
        'difficulty' => 'medium',
        'types' => ['multiple_choice'],
    ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('ai_generations', [
        'material_id' => $material->id,
        'creator_id' => $creator->id,
        'status' => 'queued',
    ]);
});

it('blocks generation with 429 when creator reaches weekly quota', function () {
    Bus::fake();
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    app(TenantContext::class)->set($organization);

    $material = Material::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'disk' => 'local',
        'path' => 'doc.pdf',
        'original_name' => 'doc.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
        'status' => 'extracted',
    ]);

    // Create 10 generations for this creator within the current week
    for ($i = 0; $i < 10; $i++) {
        AiGeneration::create([
            'organization_id' => $organization->id,
            'creator_id' => $creator->id,
            'material_id' => $material->id,
            'status' => 'completed',
            'question_count' => 5,
            'difficulty' => 'easy',
            'types' => ['multiple_choice'],
            'created_at' => now()->startOfWeek()->addHours($i + 1),
        ]);
    }
    app(TenantContext::class)->clear();

    $this->post(route('ai-generations.store', $material), [
        'question_count' => 5,
        'difficulty' => 'medium',
        'types' => ['multiple_choice'],
    ])->assertStatus(429);
});

it('does not count generations from past weeks against current weekly quota', function () {
    Bus::fake();
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    app(TenantContext::class)->set($organization);

    $material = Material::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'disk' => 'local',
        'path' => 'doc.pdf',
        'original_name' => 'doc.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
        'status' => 'extracted',
    ]);

    // Create 10 generations from previous week
    for ($i = 0; $i < 10; $i++) {
        AiGeneration::forceCreate([
            'organization_id' => $organization->id,
            'creator_id' => $creator->id,
            'material_id' => $material->id,
            'status' => 'completed',
            'question_count' => 5,
            'difficulty' => 'easy',
            'types' => ['multiple_choice'],
            'created_at' => now()->subWeeks(1)->startOfWeek()->addHours($i + 1),
            'updated_at' => now()->subWeeks(1)->startOfWeek()->addHours($i + 1),
        ]);
    }
    app(TenantContext::class)->clear();

    $this->post(route('ai-generations.store', $material), [
        'question_count' => 5,
        'difficulty' => 'medium',
        'types' => ['multiple_choice'],
    ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});

it('isolates weekly quota per creator', function () {
    Bus::fake();
    $organization = Organization::factory()->create();
    $creator1 = User::factory()->create();
    $creator2 = User::factory()->create();
    $organization->members()->attach($creator1, ['role' => 'creator']);
    $organization->members()->attach($creator2, ['role' => 'creator']);

    app(TenantContext::class)->set($organization);
    $material = Material::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator1->id,
        'disk' => 'local',
        'path' => 'doc.pdf',
        'original_name' => 'doc.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
        'status' => 'extracted',
    ]);

    // Creator 1 exhausts all 10 weekly quotas
    for ($i = 0; $i < 10; $i++) {
        AiGeneration::create([
            'organization_id' => $organization->id,
            'creator_id' => $creator1->id,
            'material_id' => $material->id,
            'status' => 'completed',
            'question_count' => 5,
            'difficulty' => 'easy',
            'types' => ['multiple_choice'],
            'created_at' => now()->startOfWeek()->addHours($i + 1),
        ]);
    }
    app(TenantContext::class)->clear();

    // Creator 2 should still be able to generate
    $this->actingAs($creator2)->post(route('organizations.switch', $organization));
    $this->post(route('ai-generations.store', $material), [
        'question_count' => 5,
        'difficulty' => 'medium',
        'types' => ['multiple_choice'],
    ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});

it('passes accurate weekly quota to materials page inertia view', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    app(TenantContext::class)->set($organization);

    $material = Material::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'disk' => 'local',
        'path' => 'doc.pdf',
        'original_name' => 'doc.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
        'status' => 'extracted',
    ]);

    // Create 3 generations this week
    for ($i = 0; $i < 3; $i++) {
        AiGeneration::create([
            'organization_id' => $organization->id,
            'creator_id' => $creator->id,
            'material_id' => $material->id,
            'status' => 'completed',
            'question_count' => 5,
            'difficulty' => 'easy',
            'types' => ['multiple_choice'],
            'created_at' => now()->startOfWeek()->addHours($i + 1),
        ]);
    }
    app(TenantContext::class)->clear();

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->get(route('materials.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Materials')
            ->has('quota')
            ->where('quota.weekly_limit', 10)
            ->where('quota.weekly_used', 3)
            ->where('quota.weekly_remaining', 7)
        );
});
