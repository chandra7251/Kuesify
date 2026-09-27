<?php

use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Support\TenantContext;

it('lets a participant complete an objective self-paced quiz', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    $organization->members()->attach([$creator->id => ['role' => 'creator'], $participant->id => ['role' => 'participant']]);
    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Mandiri', 'status' => 'published']);
    $question = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'multiple_choice', 'prompt' => '2 + 3?', 'options' => ['4', '5'], 'correct_answer' => '5', 'points' => 100]);
    $quiz->questions()->attach($question, ['position' => 1]);
    app(TenantContext::class)->clear();
    $this->actingAs($participant)->post(route('organizations.switch', $organization));

    $this->post(route('attempts.store', $quiz))->assertRedirect();
    $attempt = QuizAttempt::withoutGlobalScopes()->firstOrFail();
    $this->put(route('attempts.answers.upsert', [$attempt, $question]), ['answer' => '5'])->assertRedirect();
    $this->post(route('attempts.submit', $attempt))->assertRedirect();

    $this->assertDatabaseHas('quiz_attempts', ['id' => $attempt->id, 'status' => 'completed', 'score' => 100]);
});

it('opens study flashcards only after a published quiz deadline passes', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    $organization->members()->attach([$creator->id => ['role' => 'creator'], $participant->id => ['role' => 'participant']]);

    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Study', 'status' => 'published', 'deadline_at' => now()->addDay()]);
    $question = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'fill_blank', 'prompt' => 'Ibu kota?', 'correct_answer' => 'Jakarta', 'explanation' => 'DKI Jakarta.']);
    $quiz->questions()->attach($question, ['position' => 1]);
    app(TenantContext::class)->clear();

    $this->actingAs($participant)->post(route('organizations.switch', $organization));
    $this->getJson(route('quizzes.study', $quiz))->assertForbidden();

    $quiz->update(['deadline_at' => now()->subMinute()]);

    $this->getJson(route('quizzes.study', $quiz))
        ->assertOk()
        ->assertJsonPath('quiz.flashcards.0.answer', 'Jakarta')
        ->assertJsonPath('quiz.flashcards.0.explanation', 'DKI Jakarta.');

    $this->get(route('quizzes.study', $quiz))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('StudyMode')
            ->where('quiz.flashcards.0.answer', 'Jakarta'));
});
