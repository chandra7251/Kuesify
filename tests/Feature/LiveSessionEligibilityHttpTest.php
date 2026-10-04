<?php

use App\Models\LiveSession;
use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Event;

function makeCreatorWithOrganization(): array
{
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);

    return [$organization, $creator];
}

function makePublishedQuizWithQuestion(Organization $organization, User $creator, string $type = 'multiple_choice'): Quiz
{
    app(TenantContext::class)->set($organization);

    $quiz = Quiz::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'title' => $type === 'essay' ? 'Essay Live Blocked' : 'Objective Live',
        'status' => 'published',
    ]);

    $question = Question::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'type' => $type,
        'prompt' => 'Pertanyaan live?',
        'options' => $type === 'multiple_choice' ? ['A', 'B'] : null,
        'correct_answer' => $type === 'multiple_choice' ? 'A' : null,
        'points' => 100,
    ]);

    $quiz->questions()->attach($question, ['position' => 1]);
    app(TenantContext::class)->clear();

    return $quiz;
}

afterEach(fn () => app(TenantContext::class)->clear());

it('lets a teacher create a live session from an eligible quiz', function () {
    Event::fake();
    [$organization, $creator] = makeCreatorWithOrganization();
    $quiz = makePublishedQuizWithQuestion($organization, $creator);

    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    $response = $this->post(route('live-sessions.store'), [
        'quiz_id' => $quiz->id,
        'question_duration' => 30,
        'speed_multiplier' => 10,
    ]);

    $session = LiveSession::withoutGlobalScopes()->where('quiz_id', $quiz->id)->firstOrFail();
    $response->assertRedirect(route('live-sessions.play', $session));
    $this->assertDatabaseHas('live_sessions', [
        'quiz_id' => $quiz->id,
        'host_id' => $creator->id,
        'status' => 'lobby',
    ]);
});

it('does not create a live session or return 500 for a quiz containing essay questions', function () {
    Event::fake();
    [$organization, $creator] = makeCreatorWithOrganization();
    $quiz = makePublishedQuizWithQuestion($organization, $creator, 'essay');

    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    $response = $this
        ->from(route('live-sessions.index'))
        ->post(route('live-sessions.store'), [
            'quiz_id' => $quiz->id,
            'question_duration' => 30,
            'speed_multiplier' => 10,
        ]);

    $response
        ->assertRedirect(route('live-sessions.index'))
        ->assertSessionHasErrors([
            'quiz_id' => 'Quiz ini memiliki soal essay dan belum dapat digunakan dalam Live Quiz.',
        ])
        ->assertSessionHas('_old_input.quiz_id');

    $this->assertDatabaseMissing('live_sessions', ['quiz_id' => $quiz->id]);
});

it('marks published essay quizzes as ineligible on the live session page', function () {
    [$organization, $creator] = makeCreatorWithOrganization();
    $essayQuiz = makePublishedQuizWithQuestion($organization, $creator, 'essay');
    $eligibleQuiz = makePublishedQuizWithQuestion($organization, $creator);

    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    $this->get(route('live-sessions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('LiveHub')
            ->where('quizzes.0.id', $essayQuiz->id)
            ->where('quizzes.0.has_essay_questions', true)
            ->where('quizzes.1.id', $eligibleQuiz->id)
            ->where('quizzes.1.has_essay_questions', false)
        );
});
