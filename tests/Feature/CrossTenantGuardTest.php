<?php

use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Support\TenantContext;

afterEach(fn () => app(TenantContext::class)->clear());

it('blocks participant from accessing quiz builder page', function () {
    $organization = Organization::factory()->create();
    $participant = User::factory()->create();
    $organization->members()->attach($participant, ['role' => 'participant']);

    $this->actingAs($participant)->post(route('organizations.switch', $organization));
    $this->get(route('quizzes.index'))->assertForbidden();
});

it('blocks participant from creating quiz', function () {
    $organization = Organization::factory()->create();
    $participant = User::factory()->create();
    $organization->members()->attach($participant, ['role' => 'participant']);

    $this->actingAs($participant)->post(route('organizations.switch', $organization));
    $this->post(route('quizzes.store'), ['title' => 'Hack'])->assertForbidden();
});

it('blocks cross-tenant quiz update (creator from org A cannot edit org B quiz)', function () {
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    $creatorA = User::factory()->create();
    $creatorB = User::factory()->create();
    $orgA->members()->attach($creatorA, ['role' => 'creator']);
    $orgB->members()->attach($creatorB, ['role' => 'creator']);

    app(TenantContext::class)->set($orgB);
    $quiz = Quiz::create(['organization_id' => $orgB->id, 'creator_id' => $creatorB->id, 'title' => 'Org B Quiz', 'status' => 'draft']);
    app(TenantContext::class)->clear();

    $this->actingAs($creatorA)->post(route('organizations.switch', $orgA));
    $this->patch(route('quizzes.update', $quiz), ['title' => 'Hijacked'])->assertForbidden();

    $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id, 'title' => 'Hijacked']);
});

it('blocks cross-tenant quiz publish', function () {
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    $creatorA = User::factory()->create();
    $creatorB = User::factory()->create();
    $orgA->members()->attach($creatorA, ['role' => 'creator']);
    $orgB->members()->attach($creatorB, ['role' => 'creator']);

    app(TenantContext::class)->set($orgB);
    $quiz = Quiz::create(['organization_id' => $orgB->id, 'creator_id' => $creatorB->id, 'title' => 'Draft B', 'status' => 'draft']);
    $question = Question::create(['organization_id' => $orgB->id, 'creator_id' => $creatorB->id, 'type' => 'true_false', 'prompt' => 'Q', 'correct_answer' => 'true']);
    $quiz->questions()->attach($question, ['position' => 1]);
    app(TenantContext::class)->clear();

    $this->actingAs($creatorA)->post(route('organizations.switch', $orgA));
    $this->post(route('quizzes.publish', $quiz))->assertForbidden();

    $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'status' => 'draft']);
});

it('blocks cross-tenant attempt play (participant A cannot see participant B attempt)', function () {
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    $participantA = User::factory()->create();
    $participantB = User::factory()->create();
    $creatorB = User::factory()->create();
    $orgA->members()->attach($participantA, ['role' => 'participant']);
    $orgB->members()->attach([$participantB->id => ['role' => 'participant'], $creatorB->id => ['role' => 'creator']]);

    app(TenantContext::class)->set($orgB);
    $quiz = Quiz::create(['organization_id' => $orgB->id, 'creator_id' => $creatorB->id, 'title' => 'QB', 'status' => 'published']);
    $attempt = \App\Models\QuizAttempt::create(['organization_id' => $orgB->id, 'quiz_id' => $quiz->id, 'participant_id' => $participantB->id, 'status' => 'in_progress']);
    app(TenantContext::class)->clear();

    $this->actingAs($participantA)->post(route('organizations.switch', $orgA));
    $this->get(route('attempts.play', $attempt))->assertForbidden();
});

it('creator can access materials page but participant cannot', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    $organization->members()->attach([$creator->id => ['role' => 'creator'], $participant->id => ['role' => 'participant']]);

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->get(route('materials.index'))->assertOk()->assertInertia(fn ($page) => $page->component('Materials'));

    $this->actingAs($participant)->post(route('organizations.switch', $organization));
    $this->get(route('materials.index'))->assertForbidden();
});

it('creator can export report but participant cannot', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    $organization->members()->attach([$creator->id => ['role' => 'creator'], $participant->id => ['role' => 'participant']]);

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->get(route('reports.export'))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $this->actingAs($participant)->post(route('organizations.switch', $organization));
    $this->get(route('reports.export'))->assertForbidden();
});
