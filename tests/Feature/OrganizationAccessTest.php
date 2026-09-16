<?php

use App\Models\Organization;
use App\Models\User;

it('blocks switching to an organization without membership', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();

    $this->actingAs($user)
        ->post(route('organizations.switch', $organization))
        ->assertForbidden();
});

it('allows a creator to create a quiz in active organization', function () {
    $creator = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);

    $this->actingAs($creator)->post(route('organizations.switch', $organization))->assertRedirect();

    $this->post(route('quizzes.store'), ['title' => 'Kuis pertama'])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('quizzes', ['organization_id' => $organization->id, 'creator_id' => $creator->id, 'title' => 'Kuis pertama']);
});

it('blocks participants from creating a quiz', function () {
    $participant = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($participant, ['role' => 'participant']);

    $this->actingAs($participant)->post(route('organizations.switch', $organization));

    $this->post(route('quizzes.store'), ['title' => 'Tidak boleh'])->assertForbidden();
});
