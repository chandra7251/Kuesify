<?php

use App\Models\AiGeneration;
use App\Models\AiQuestionDraft;
use App\Models\Material;
use App\Models\Organization;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Bus;

afterEach(fn () => app(TenantContext::class)->clear());

it('queues a configured Gemini generation only for extracted material', function () {
    Bus::fake();
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    app(TenantContext::class)->set($organization);
    $material = Material::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'disk' => 'local', 'path' => 'x.pdf', 'original_name' => 'x.pdf', 'mime_type' => 'application/pdf', 'size' => 1, 'status' => 'extracted']);
    app(TenantContext::class)->clear();

    $this->post(route('ai-generations.store', $material), ['question_count' => 5, 'difficulty' => 'medium', 'types' => ['multiple_choice']])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('ai_generations', ['material_id' => $material->id, 'status' => 'queued', 'question_count' => 5]);
    Bus::assertDispatched(\App\Jobs\GenerateQuestions::class);
});

it('requires manual approval before an AI draft becomes a question', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    app(TenantContext::class)->set($organization);
    $generation = AiGeneration::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'material_id' => Material::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'disk' => 'local', 'path' => 'x.pdf', 'original_name' => 'x.pdf', 'mime_type' => 'application/pdf', 'size' => 1, 'status' => 'extracted'])->id, 'status' => 'review', 'question_count' => 5, 'difficulty' => 'medium', 'types' => ['multiple_choice']]);
    $draft = AiQuestionDraft::create(['ai_generation_id' => $generation->id, 'type' => 'multiple_choice', 'prompt' => '2 + 2?', 'options' => ['3', '4'], 'correct_answer' => '4', 'points' => 1000, 'status' => 'pending']);

    expect($draft->approve($creator)->prompt)->toBe('2 + 2?')
        ->and($draft->fresh()->status)->toBe('approved');
});

it('lets only generation creator approve a draft through HTTP', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    app(TenantContext::class)->set($organization);
    $material = Material::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'disk' => 'local', 'path' => 'x.pdf', 'original_name' => 'x.pdf', 'mime_type' => 'application/pdf', 'size' => 1, 'status' => 'extracted']);
    $generation = AiGeneration::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'material_id' => $material->id, 'status' => 'review', 'question_count' => 5, 'difficulty' => 'medium', 'types' => ['multiple_choice']]);
    $draft = AiQuestionDraft::create(['ai_generation_id' => $generation->id, 'type' => 'multiple_choice', 'prompt' => '3 + 3?', 'options' => ['5', '6'], 'correct_answer' => '6', 'points' => 1000, 'status' => 'pending']);
    app(TenantContext::class)->clear();

    $this->post(route('ai-drafts.approve', $draft))->assertRedirect()->assertSessionHasNoErrors();

    $this->assertDatabaseHas('ai_question_drafts', ['id' => $draft->id, 'status' => 'approved']);
    $this->assertDatabaseHas('questions', ['organization_id' => $organization->id, 'prompt' => '3 + 3?']);
});

it('lets generation creator edit or reject pending draft only', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    app(TenantContext::class)->set($organization);
    $material = Material::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'disk' => 'local', 'path' => 'x.pdf', 'original_name' => 'x.pdf', 'mime_type' => 'application/pdf', 'size' => 1, 'status' => 'extracted']);
    $generation = AiGeneration::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'material_id' => $material->id, 'status' => 'review', 'question_count' => 5, 'difficulty' => 'medium', 'types' => ['fill_blank']]);
    $draft = AiQuestionDraft::create(['ai_generation_id' => $generation->id, 'type' => 'fill_blank', 'prompt' => 'Lama', 'correct_answer' => 'jawab', 'points' => 1000, 'status' => 'pending']);
    app(TenantContext::class)->clear();

    $this->patch(route('ai-drafts.update', $draft), ['type' => 'fill_blank', 'prompt' => 'Baru', 'correct_answer' => 'jawab', 'points' => 500])->assertRedirect();
    $this->post(route('ai-drafts.reject', $draft))->assertRedirect();
    $this->assertDatabaseHas('ai_question_drafts', ['id' => $draft->id, 'prompt' => 'Baru', 'status' => 'rejected']);
});
