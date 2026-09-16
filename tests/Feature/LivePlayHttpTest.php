<?php

use App\Models\LiveSession;
use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Event;

it('lets host start and guest submit only through reconnect session token', function () {
    Event::fake();
    $organization = Organization::factory()->create();
    $host = User::factory()->create();
    $organization->members()->attach($host, ['role' => 'creator']);
    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $host->id, 'title' => 'Play', 'status' => 'published']);
    $question = Question::create(['organization_id' => $organization->id, 'creator_id' => $host->id, 'type' => 'true_false', 'prompt' => 'Benar?', 'correct_answer' => 'true', 'points' => 100]);
    $quiz->questions()->attach($question, ['position' => 1]);
    $session = LiveSession::open($quiz, $host, 30, 10);
    app(TenantContext::class)->clear();

    $this->actingAs($host)->post(route('organizations.switch', $organization));
    $this->get(route('live-sessions.play', $session))->assertOk()->assertInertia(fn ($page) => $page->component('LivePlay'));
    $this->post(route('live-sessions.start', $session))->assertRedirect();

    $guest = $this->post(route('live-sessions.join'), ['pin' => $session->pin, 'alias' => 'Rani']);
    $guest->assertRedirect();
    $this->post(route('live-sessions.answers.store', $session), ['answer' => 'true'])->assertRedirect();

    $this->assertDatabaseHas('live_answers', ['question_id' => $question->id, 'is_correct' => true]);
    Event::assertDispatched(\App\Events\LiveSessionStateChanged::class);
});

it('lets host lock, advance, kick, end, and guest reconnect with token', function () {
    $organization = Organization::factory()->create();
    $host = User::factory()->create();
    $organization->members()->attach($host, ['role' => 'creator']);
    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $host->id, 'title' => 'Kontrol', 'status' => 'published']);
    $first = Question::create(['organization_id' => $organization->id, 'creator_id' => $host->id, 'type' => 'true_false', 'prompt' => 'A', 'correct_answer' => 'true']);
    $second = Question::create(['organization_id' => $organization->id, 'creator_id' => $host->id, 'type' => 'true_false', 'prompt' => 'B', 'correct_answer' => 'false']);
    $quiz->questions()->attach([$first->id => ['position' => 1], $second->id => ['position' => 2]]);
    $session = LiveSession::open($quiz, $host, 30, 10);
    $guest = $session->joinGuest('Rani');
    app(TenantContext::class)->clear();

    $this->actingAs($host)->post(route('organizations.switch', $organization));
    $this->post(route('live-sessions.lock', $session))->assertRedirect();
    $this->post(route('live-sessions.start', $session))->assertRedirect();
    $this->post(route('live-sessions.next', $session))->assertRedirect();
    $this->post(route('live-sessions.kick', [$session, $guest]))->assertRedirect();
    $this->post(route('live-sessions.end', $session))->assertRedirect();

    $this->post(route('live-sessions.reconnect', $session), ['token' => $guest->reconnect_token])->assertRedirect();
    $this->assertDatabaseHas('live_sessions', ['id' => $session->id, 'status' => 'ended', 'lobby_locked' => true]);
});
