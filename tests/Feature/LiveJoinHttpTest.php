<?php

use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;

it('lets a creator open a session and a guest join by PIN', function () {
    $creator = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    app(\App\Support\TenantContext::class)->set($organization);
    $quiz = Quiz::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Live', 'status' => 'published']);
    $question = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'true_false', 'prompt' => 'Benar?', 'correct_answer' => 'true']);
    $quiz->questions()->attach($question, ['position' => 1]);
    app(\App\Support\TenantContext::class)->clear();

    $this->post(route('live-sessions.store'), ['quiz_id' => $quiz->id])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
    $pin = \App\Models\LiveSession::withoutGlobalScopes()->firstOrFail()->pin;

    $this->post(route('live-sessions.join'), ['pin' => $pin, 'alias' => 'Rani'])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('live_participants', ['alias' => 'Rani']);
});
