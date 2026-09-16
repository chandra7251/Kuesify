<?php

use App\Models\LiveSession;
use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Carbon;

afterEach(fn () => app(TenantContext::class)->clear());

it('scores one on-time guest answer on server', function () {
    Carbon::setTestNow('2026-09-15 10:00:00');
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Live', 'status' => 'published']);
    $question = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'multiple_choice', 'prompt' => '2+2', 'options' => ['3', '4'], 'correct_answer' => '4', 'points' => 1000]);
    $quiz->questions()->attach($question, ['position' => 1]);
    $session = LiveSession::open($quiz, $creator, 30, 10);
    $participant = $session->joinGuest('Rani');
    $session->start();

    Carbon::setTestNow('2026-09-15 10:00:05');
    $answer = $session->submit($participant, $question, '4');

    expect($session->pin)->toHaveLength(6)
        ->and($answer->points_awarded)->toBe(1250)
        ->and(fn () => $session->submit($participant, $question, '4'))->toThrow(\DomainException::class);
});
