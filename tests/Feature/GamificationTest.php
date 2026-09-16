<?php

use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\GamificationService;
use App\Support\TenantContext;

afterEach(fn () => app(TenantContext::class)->clear());

it('awards XP and badges once for a completed attempt', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'XP', 'status' => 'published']);
    $question = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'true_false', 'prompt' => 'Benar?', 'correct_answer' => 'true', 'points' => 100]);
    $quiz->questions()->attach($question, ['position' => 1]);
    $attempt = QuizAttempt::start($quiz, $participant);
    $attempt->answer($question, 'true');
    $attempt->submit();

    app(GamificationService::class)->recordAttempt($attempt);
    app(GamificationService::class)->recordAttempt($attempt);

    $this->assertDatabaseHas('user_progresses', ['organization_id' => $organization->id, 'user_id' => $participant->id, 'xp' => 100, 'level' => 1]);
    $this->assertDatabaseCount('xp_events', 1);
    $this->assertDatabaseHas('badge_awards', ['user_id' => $participant->id]);
});
