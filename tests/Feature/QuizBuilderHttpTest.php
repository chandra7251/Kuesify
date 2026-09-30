<?php

use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Support\TenantContext;

it('lets creator configure, order, and publish an own quiz', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Awal', 'status' => 'draft']);
    $first = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'true_false', 'prompt' => 'A', 'correct_answer' => 'true']);
    $second = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'fill_blank', 'prompt' => 'B', 'correct_answer' => 'B']);
    app(TenantContext::class)->clear();
    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    $this->patch(route('quizzes.update', $quiz), ['title' => 'Final', 'description' => 'Deskripsi', 'visibility' => 'private', 'max_attempts' => 2])
        ->assertRedirect()->assertSessionHasNoErrors();
    $this->put(route('quizzes.questions.sync', $quiz), ['question_ids' => [$second->id, $first->id]])
        ->assertRedirect()->assertSessionHasNoErrors();
    $this->post(route('quizzes.publish', $quiz))->assertRedirect();

    $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'title' => 'Final', 'status' => 'published', 'visibility' => 'private']);
    $this->assertDatabaseHas('quiz_question', ['quiz_id' => $quiz->id, 'question_id' => $second->id, 'position' => 1]);
});

it('lets organization admin manage any tenant quiz and creator clone and archive own quiz', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $admin = User::factory()->create();
    $organization->members()->attach([$creator->id => ['role' => 'creator'], $admin->id => ['role' => 'organization_admin']]);
    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Asal', 'status' => 'draft']);
    $question = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'true_false', 'prompt' => 'A', 'correct_answer' => 'true']);
    $quiz->questions()->attach($question, ['position' => 1]);
    app(TenantContext::class)->clear();

    $this->actingAs($admin)->post(route('organizations.switch', $organization));
    $this->patch(route('quizzes.update', $quiz), ['title' => 'Dikelola admin'])->assertRedirect();

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->post(route('quizzes.clone', $quiz))->assertRedirect();
    $clone = Quiz::withoutGlobalScopes()->where('title', 'Dikelola admin (copy)')->firstOrFail();
    $this->post(route('quizzes.archive', $clone))->assertRedirect();

    $this->assertDatabaseHas('quizzes', ['id' => $clone->id, 'status' => 'archived']);
    $this->assertDatabaseHas('quiz_question', ['quiz_id' => $clone->id, 'question_id' => $question->id]);
});

it('queues public quizzes for super admin moderation before publication', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $superAdmin = User::factory()->create();
    $participant = User::factory()->create();
    $organization->members()->attach([
        $creator->id => ['role' => 'creator'],
        $superAdmin->id => ['role' => 'super_admin'],
        $participant->id => ['role' => 'participant'],
    ]);

    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Publik', 'status' => 'draft', 'visibility' => 'public']);
    $question = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'true_false', 'prompt' => 'A', 'correct_answer' => 'true']);
    $quiz->questions()->attach($question, ['position' => 1]);
    app(TenantContext::class)->clear();

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->post(route('quizzes.publish', $quiz))->assertRedirect();
    $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'status' => 'pending_moderation']);

    $this->actingAs($participant)->post(route('organizations.switch', $organization));
    $this->getJson(route('admin.moderation.index'))->assertForbidden();

    $this->actingAs($superAdmin)->post(route('organizations.switch', $organization));
    $this->getJson(route('admin.moderation.index'))
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Publik');
    $this->post(route('admin.moderation.approve', $quiz))->assertRedirect();

    $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'status' => 'published']);
});

it('lets quiz owners add collaborator creators who can edit the quiz', function () {
    $organization = Organization::factory()->create();
    $owner = User::factory()->create();
    $collaborator = User::factory()->create();
    $organization->members()->attach([
        $owner->id => ['role' => 'creator'],
        $collaborator->id => ['role' => 'creator'],
    ]);

    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $owner->id, 'title' => 'Kolaborasi', 'status' => 'draft']);
    app(TenantContext::class)->clear();

    $this->actingAs($owner)->post(route('organizations.switch', $organization));
    $this->post(route('quizzes.collaborators.store', $quiz), ['user_id' => $collaborator->id])->assertRedirect();
    $this->assertDatabaseHas('quiz_collaborator', ['quiz_id' => $quiz->id, 'user_id' => $collaborator->id]);

    $this->actingAs($collaborator)->post(route('organizations.switch', $organization));
    $this->patch(route('quizzes.update', $quiz), ['title' => 'Diedit bersama'])->assertRedirect();

    $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'title' => 'Diedit bersama']);
});
