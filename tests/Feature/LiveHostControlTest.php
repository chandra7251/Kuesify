<?php

use App\Models\LiveSession;
use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Support\TenantContext;

afterEach(fn () => app(TenantContext::class)->clear());

it('controls lobby, participant access, and question progression', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Host', 'status' => 'published']);
    $first = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'true_false', 'prompt' => 'A', 'correct_answer' => 'true']);
    $second = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'true_false', 'prompt' => 'B', 'correct_answer' => 'false']);
    $quiz->questions()->attach([$first->id => ['position' => 1], $second->id => ['position' => 2]]);
    $session = LiveSession::open($quiz, $creator, 30, 10);
    $participant = $session->joinGuest('Rani');

    expect($session->status)->toBe('lobby');
    $session->lockLobby();
    expect(fn () => $session->joinGuest('Budi'))->toThrow(\DomainException::class);
    $session->start();
    $session->kick($participant);
    $session->nextQuestion();
    $session->nextQuestion();

    expect($session->fresh()->status)->toBe('ended')
        ->and($participant->fresh()->kicked_at)->not->toBeNull();
});
