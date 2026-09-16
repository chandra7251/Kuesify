<?php

use App\Models\Organization;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Carbon;

afterEach(fn () => app(TenantContext::class)->clear());

it('blocks attempts after homework deadline', function () {
    Carbon::setTestNow('2026-09-15 12:00:00');
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Terlambat', 'status' => 'published', 'deadline_at' => now()->subMinute()]);

    expect(fn () => QuizAttempt::start($quiz, $participant))->toThrow(\DomainException::class);
});

it('blocks attempts after maximum is reached', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Sekali', 'status' => 'published', 'max_attempts' => 1]);
    QuizAttempt::start($quiz, $participant);

    expect(fn () => QuizAttempt::start($quiz, $participant))->toThrow(\DomainException::class);
});
