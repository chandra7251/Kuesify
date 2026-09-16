<?php

use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Support\TenantContext;

it('allows quiz creator to grade a pending essay answer', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    $organization->members()->attach([$creator->id => ['role' => 'creator'], $participant->id => ['role' => 'participant']]);
    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Essay', 'status' => 'published']);
    $question = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'essay', 'prompt' => 'Jelaskan', 'points' => 100]);
    $quiz->questions()->attach($question, ['position' => 1]);
    $attempt = QuizAttempt::start($quiz, $participant);
    $answer = $attempt->answer($question, 'Jawaban');
    $attempt->submit();
    app(TenantContext::class)->clear();
    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    $this->post(route('attempts.answers.grade', [$attempt, $answer]), ['points' => 85, 'feedback' => 'Bagus'])
        ->assertRedirect()->assertSessionHasNoErrors();

    $this->assertDatabaseHas('quiz_attempts', ['id' => $attempt->id, 'status' => 'completed', 'score' => 85]);
});
