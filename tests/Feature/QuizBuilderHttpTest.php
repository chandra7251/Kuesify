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
