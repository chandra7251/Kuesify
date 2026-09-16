<?php

use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Support\TenantContext;

afterEach(fn () => app(TenantContext::class)->clear());

it('keeps essay attempts pending manual review', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Homework', 'status' => 'published']);
    $objective = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'true_false', 'prompt' => 'Benar?', 'correct_answer' => 'true', 'points' => 100]);
    $essay = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'essay', 'prompt' => 'Jelaskan.', 'points' => 100]);
    $quiz->questions()->attach([$objective->id => ['position' => 1], $essay->id => ['position' => 2]]);

    $attempt = QuizAttempt::start($quiz, $participant);
    $attempt->answer($objective, 'true');
    $attempt->answer($essay, 'Karena materi.');
    $attempt->submit();

    expect($attempt->fresh()->status)->toBe('pending_review')
        ->and($attempt->fresh()->score)->toBe(100);
});

it('finalizes score after creator grades essay', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Review', 'status' => 'published']);
    $essay = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'essay', 'prompt' => 'Jelaskan.', 'points' => 100]);
    $quiz->questions()->attach($essay, ['position' => 1]);
    $attempt = QuizAttempt::start($quiz, $participant);
    $attempt->answer($essay, 'Jawaban peserta.');
    $attempt->submit();

    $attempt->gradeEssay($attempt->answers()->first(), 80, 'Sudah tepat.');

    expect($attempt->fresh()->status)->toBe('completed')
        ->and($attempt->fresh()->score)->toBe(80);
});
