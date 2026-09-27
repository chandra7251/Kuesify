<?php

use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Notifications\AttemptGraded;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Notification;

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

it('notifies participant when an essay attempt is graded', function () {
    Notification::fake();
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    $organization->members()->attach([$creator->id => ['role' => 'creator'], $participant->id => ['role' => 'participant']]);

    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Essay', 'status' => 'published']);
    $question = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'essay', 'prompt' => 'Jelaskan.', 'points' => 10]);
    $quiz->questions()->attach($question, ['position' => 1]);
    $attempt = QuizAttempt::create(['organization_id' => $organization->id, 'quiz_id' => $quiz->id, 'participant_id' => $participant->id, 'status' => 'pending_review']);
    $answer = $attempt->answers()->create(['question_id' => $question->id, 'answer' => 'Jawaban']);
    app(TenantContext::class)->clear();

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->post(route('attempts.answers.grade', [$attempt, $answer]), ['points' => 8])->assertRedirect();

    Notification::assertSentTo($participant, AttemptGraded::class);
});
