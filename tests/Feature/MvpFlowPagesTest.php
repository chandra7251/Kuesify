<?php

use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Support\TenantContext;

it('renders creator builder and blocks participant role', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    $organization->members()->attach([$creator->id => ['role' => 'creator'], $participant->id => ['role' => 'participant']]);
    app(TenantContext::class)->set($organization);
    Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Draft', 'status' => 'draft']);
    Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'true_false', 'prompt' => 'Benar', 'correct_answer' => 'true']);
    app(TenantContext::class)->clear();

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->get(route('quizzes.index'))->assertOk()->assertInertia(fn ($page) => $page->component('QuizBuilder')->has('quizzes', 1)->has('questions', 1));

    $this->actingAs($participant)->post(route('organizations.switch', $organization));
    $this->get(route('quizzes.index'))->assertForbidden();
});

it('selects first active organization when session has no organization yet', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator', 'is_active' => true]);

    $this->actingAs($creator)->get(route('dashboard'))->assertOk();
    expect(session('organization_id'))->toBe($organization->id);
});

it('renders participant player, creator gradebook, and tenant CSV export', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    $organization->members()->attach([$creator->id => ['role' => 'creator'], $participant->id => ['role' => 'participant']]);
    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Mandiri', 'status' => 'published']);
    $question = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'true_false', 'prompt' => 'Benar', 'correct_answer' => 'true']);
    $quiz->questions()->attach($question, ['position' => 1]);
    $attempt = QuizAttempt::create(['organization_id' => $organization->id, 'quiz_id' => $quiz->id, 'participant_id' => $participant->id, 'status' => 'in_progress']);
    app(TenantContext::class)->clear();

    $this->actingAs($participant)->post(route('organizations.switch', $organization));
    $this->get(route('attempts.index'))->assertOk()->assertInertia(fn ($page) => $page->component('Attempts')->has('publishedQuizzes', 1));
    $this->get(route('attempts.play', $attempt))->assertOk()->assertInertia(fn ($page) => $page->component('AttemptPlay')->has('attempt.quiz.questions', 1));

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->get(route('attempts.index'))->assertOk()->assertInertia(fn ($page) => $page->component('Attempts')->where('gradebook', true));
    $this->get(route('attempts.export'))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
});
