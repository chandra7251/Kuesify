<?php

use App\Models\AttemptAnswer;
use App\Models\Organization;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\Quiz;
use App\Models\User;
use App\Models\UserProgress;
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

it('renders creator dashboard with tenant stats', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($user, ['role' => 'creator']);
    app(TenantContext::class)->set($organization);
    Quiz::create(['organization_id' => $organization->id, 'creator_id' => $user->id, 'title' => 'Stat', 'status' => 'draft']);
    app(TenantContext::class)->clear();

    $this->actingAs($user)->post(route('organizations.switch', $organization));
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('creator/dashboard')
            ->where('organization.name', $organization->name)
            ->where('organization.role', 'creator')
            ->where('stats.quizzes', 1));
});

it('renders creator dashboard learning insights', function () {
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $organization->members()->attach($participant, ['role' => 'participant']);

    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Analisis Pecahan', 'status' => 'published']);
    $question = Question::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'type' => 'true_false',
        'prompt' => 'Pecahan 1/2 sama dengan 0.5',
        'correct_answer' => 'true',
    ]);
    $attempt = QuizAttempt::create([
        'organization_id' => $organization->id,
        'quiz_id' => $quiz->id,
        'participant_id' => $participant->id,
        'status' => 'completed',
        'score' => 0,
    ]);
    $answer = AttemptAnswer::create([
        'quiz_attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'answer' => 'false',
        'is_correct' => false,
    ]);
    DB::table('quiz_attempts')->where('id', $attempt->id)->update(['created_at' => now()->subSeconds(30), 'updated_at' => now()->subSeconds(30)]);
    DB::table('attempt_answers')->where('id', $answer->id)->update(['created_at' => now(), 'updated_at' => now()]);
    app(TenantContext::class)->clear();

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('creator/dashboard')
            ->where('stats.quizzes', 1)
            ->where('stats.questions', 1)
            ->where('stats.attempts', 1)
            ->where('analytics.studentRetention', 100)
            ->where('analytics.avgTimePerQuestion', 30)
            ->where('analytics.weakTopics.0.prompt', 'Pecahan 1/2 sama dengan 0.5')
            ->where('recentActivity.0.participant_name', $participant->name));
});

it('renders participant dashboard with learning progress', function () {
    $participant = User::factory()->create();
    $creator = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($participant, ['role' => 'participant']);
    $organization->members()->attach($creator, ['role' => 'creator']);

    app(TenantContext::class)->set($organization);
    $quiz = Quiz::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'title' => 'Latihan Aljabar',
        'status' => 'published',
    ]);
    QuizAttempt::create([
        'organization_id' => $organization->id,
        'quiz_id' => $quiz->id,
        'participant_id' => $participant->id,
        'status' => 'completed',
        'score' => 80,
    ]);
    UserProgress::create([
        'organization_id' => $organization->id,
        'user_id' => $participant->id,
        'xp' => 140,
        'level' => 2,
        'streak' => 3,
        'last_activity_date' => now()->toDateString(),
    ]);
    $badgeId = DB::table('badges')->insertGetId(['key' => 'first_quiz', 'name' => 'Kuis Pertama', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('badge_awards')->insert([
        'organization_id' => $organization->id,
        'user_id' => $participant->id,
        'badge_id' => $badgeId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('xp_events')->insert([
        'organization_id' => $organization->id,
        'user_id' => $participant->id,
        'quiz_attempt_id' => QuizAttempt::first()->id,
        'amount' => 80,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    app(TenantContext::class)->clear();

    $this->actingAs($participant)->post(route('organizations.switch', $organization));
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('participant/dashboard')
            ->where('organization.role', 'participant')
            ->where('stats.xp', 140)
            ->where('stats.level', 2)
            ->where('stats.streak', 3)
            ->where('stats.availableQuizzes', 1)
            ->where('stats.attempts', 1)
            ->where('badges.0.name', 'Kuis Pertama')
            ->where('quizOfTheDay.title', 'Latihan Aljabar')
            ->has('streakHeatmap', 30));
});

it('renders executive dashboard with platform analytics for super admin', function () {
    $admin = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($admin, ['role' => 'super_admin']);

    $this->actingAs($admin)->withSession([
        'organization_id' => $organization->id,
        'active_role' => 'super_admin',
    ]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('isSuperAdmin', true)
            ->has('adminAnalytics.kpi.total_organizations')
            ->has('adminAnalytics.charts.attempts_trend')
            ->has('adminAnalytics.charts.role_distribution')
            ->has('adminAnalytics.charts.top_organizations')
        );
});
