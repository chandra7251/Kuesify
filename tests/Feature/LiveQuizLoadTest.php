<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\LiveSession;
use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Services\LiveLeaderboardService;
use App\Support\TenantContext;

afterEach(fn () => app(TenantContext::class)->clear());

it('handles 100 concurrent participants joining and submitting answers atomically', function () {
    $organization = Organization::factory()->create(['timezone' => 'Asia/Jakarta']);
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);

    app(TenantContext::class)->set($organization);
    $category = Category::create(['name' => 'Load Test Category', 'slug' => 'load-test-category']);
    $quiz = Quiz::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'category_id' => $category->id,
        'title' => 'Live Load Quiz',
        'status' => 'published',
    ]);

    $q1 = Question::create([
        'organization_id' => $organization->id,
        'creator_id' => $creator->id,
        'type' => 'true_false',
        'prompt' => 'Apakah bumi bulat?',
        'correct_answer' => 'true',
        'points' => 100,
    ]);
    $quiz->questions()->attach($q1, ['position' => 1]);

    $session = LiveSession::open($quiz, $creator, 60, 5);
    expect($session->pin)->toMatch('/^\d{6}$/');

    // 100 guest join secara bersamaan
    $participants = [];
    for ($i = 1; $i <= 100; $i++) {
        $participants[] = $session->joinGuest("Peserta #{$i}");
    }

    expect($session->participants()->count())->toBe(100);

    // Host memulai sesi
    $session->start();
    expect($session->fresh()->status)->toBe('live');
    expect($session->fresh()->current_question_id)->toBe($q1->id);

    // 100 peserta jawab (50 benar, 50 salah)
    $correctCount = 0;
    foreach ($participants as $index => $participant) {
        $answer = ($index % 2 === 0) ? 'true' : 'false';
        $session->submit($participant, $q1, $answer);
        if ($answer === 'true') {
            $correctCount++;
        }
    }

    // Verifikasi database integrity
    expect($session->participants()->count())->toBe(100);
    expect(\App\Models\LiveAnswer::where('question_id', $q1->id)->count())->toBe(100);
    expect(\App\Models\LiveAnswer::where('question_id', $q1->id)->where('is_correct', true)->count())->toBe(50);
    expect(\App\Models\LiveAnswer::where('question_id', $q1->id)->where('is_correct', false)->count())->toBe(50);

    // Verifikasi leaderboard
    $leaderboard = app(LiveLeaderboardService::class)->for($session);
    expect(count($leaderboard))->toBe(100);
    // 50 peserta yang benar memiliki skor > 100 (base 100 + speed bonus)
    expect($leaderboard[0]['score'])->toBeGreaterThan(100);
    // Peserta yang salah memiliki skor 0
    expect(end($leaderboard)['score'])->toBe(0);

    // Mencegah double submit pada soal yang sama
    expect(fn () => $session->submit($participants[0], $q1, 'true'))
        ->toThrow(\DomainException::class);
});


it("handles burst HTTP join and answer submissions gracefully", function () {
    $organization = Organization::factory()->create(["timezone" => "Asia/Jakarta"]);
    $creator = User::factory()->create();
    $organization->members()->attach($creator, ["role" => "creator"]);

    app(TenantContext::class)->set($organization);
    $category = Category::create(["name" => "HTTP Load Category", "slug" => "http-load-category"]);
    $quiz = Quiz::create([
        "organization_id" => $organization->id,
        "creator_id" => $creator->id,
        "category_id" => $category->id,
        "title" => "HTTP Burst Live Quiz",
        "status" => "published",
    ]);

    $q1 = Question::create([
        "organization_id" => $organization->id,
        "creator_id" => $creator->id,
        "type" => "multiple_choice",
        "prompt" => "Manakah hewan herbivora?",
        "options" => ["Kucing", "Sapi", "Harimau", "Elang"],
        "correct_answer" => "Sapi",
        "points" => 100,
    ]);
    $quiz->questions()->attach($q1, ["position" => 1]);

    $session = LiveSession::open($quiz, $creator, 60, 5);
    $session->start();
    $pin = $session->pin;
    app(TenantContext::class)->clear();

    // 25 HTTP request joins
    $participants = [];
    for ($i = 1; $i <= 25; $i++) {
        $res = $this->post(route("live-sessions.join"), [
            "pin" => $pin,
            "alias" => "User-{$i}",
        ]);
        $res->assertRedirect();
        $stored = session("live_participant");
        expect($stored)->not->toBeNull();
        $participants[] = $stored;
    }

    expect($session->fresh()->participants()->count())->toBe(25);

    // 25 HTTP answer requests
    foreach ($participants as $idx => $p) {
        $this->withSession(["live_participant" => $p])
            ->post(route("live-sessions.answers.store", $session->id), [
                "answer" => ($idx % 2 === 0) ? "Sapi" : "Kucing",
            ])
            ->assertRedirect();
    }

    expect(\App\Models\LiveAnswer::where("question_id", $q1->id)->count())->toBe(25);
    expect(\App\Models\LiveAnswer::where("question_id", $q1->id)->where("is_correct", true)->count())->toBe(13);
});
