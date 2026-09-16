<?php

use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Support\TenantContext;

afterEach(fn () => app(TenantContext::class)->clear());

it('publishes only a quiz with questions', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Aljabar', 'status' => 'draft']);

    expect(fn () => $quiz->publish())->toThrow(\DomainException::class);

    $question = Question::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'type' => 'multiple_choice',
        'prompt' => '1 + 1',
        'options' => ['1', '2'],
        'correct_answer' => '2',
    ]);
    $quiz->questions()->attach($question, ['position' => 1]);
    $quiz->publish();

    expect($quiz->fresh()->status)->toBe('published');
});

it('keeps essay questions out of live quizzes', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Essay', 'status' => 'draft']);
    $essay = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'essay', 'prompt' => 'Jelaskan.', 'points' => 1000]);
    $quiz->questions()->attach($essay, ['position' => 1]);

    expect($quiz->isEligibleForLiveSession())->toBeFalse();
});
