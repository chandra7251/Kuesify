<?php

declare(strict_types=1);

use App\Jobs\GenerateQuestions;
use App\Models\AiGeneration;
use App\Models\Material;
use App\Models\Organization;
use App\Models\User;
use App\Services\GeminiQuestionGenerator;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;


afterEach(fn () => app(TenantContext::class)->clear());

it('lets only generation creator retry a failed generation', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $otherCreator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $organization->members()->attach($otherCreator, ['role' => 'creator']);
    app(TenantContext::class)->set($organization);
    $material = Material::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'disk' => 'local', 'path' => 'retry.pdf', 'original_name' => 'retry.pdf', 'mime_type' => 'application/pdf', 'size' => 1, 'status' => 'extracted']);
    $generation = AiGeneration::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'material_id' => $material->id, 'status' => 'failed', 'failure_reason' => 'Gemini quota habis.', 'question_count' => 5, 'difficulty' => 'medium', 'types' => ['multiple_choice']]);
    app(TenantContext::class)->clear();

    Bus::fake();
    $this->actingAs($otherCreator)->post(route('organizations.switch', $organization));
    $this->post(route('ai-generations.retry', $generation))->assertForbidden();

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->post(route('ai-generations.retry', $generation))->assertRedirect();
    $this->assertDatabaseHas('ai_generations', ['id' => $generation->id, 'status' => 'queued', 'failure_reason' => null]);
    Bus::assertDispatched(GenerateQuestions::class, fn (GenerateQuestions $job) => $job->generation->is($generation));
});

it('turns Gemini quota failure into a safe message', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.model', 'gemini-test');
    Http::fake(['https://generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'quota exceeded']], 429)]);

    expect(fn () => app(GeminiQuestionGenerator::class)->generate('materi', 5, 'medium', ['multiple_choice']))
        ->toThrow(\RuntimeException::class, 'Gemini quota habis. Coba lagi beberapa saat.');
});

it('turns invalid Gemini JSON into a safe message', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.model', 'gemini-test');
    $invalidResponse = ['candidates' => [['content' => ['parts' => [['text' => 'bukan json']]]]]];
    Http::fake(['https://generativelanguage.googleapis.com/*' => Http::response($invalidResponse, 200)]);

    expect(fn () => app(GeminiQuestionGenerator::class)->generate('materi', 5, 'medium', ['multiple_choice']))
        ->toThrow(\RuntimeException::class, 'Gemini mengembalikan format soal tidak valid. Coba lagi.');
});

it('renders materials workspace with each material creator', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    app(TenantContext::class)->set($organization);
    Material::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'disk' => 'local', 'path' => 'render.pdf', 'original_name' => 'render.pdf', 'mime_type' => 'application/pdf', 'size' => 1, 'status' => 'extracted']);
    app(TenantContext::class)->clear();

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->get(route('materials.index'))->assertOk()->assertInertia(fn ($page) => $page
        ->component('Materials')
        ->has('materials', 1)
        ->where('materials.0.creator.name', $creator->name)
    );
});
