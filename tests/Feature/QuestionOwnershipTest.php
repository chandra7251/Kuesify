<?php

use App\Models\Organization;
use App\Models\Question;
use App\Models\User;
use App\Support\TenantContext;

it('allows creator to update and delete own question only', function () {
    $organization = Organization::factory()->create();
    $creator = User::factory()->create();
    $otherCreator = User::factory()->create();
    $organization->members()->attach([$creator->id => ['role' => 'creator'], $otherCreator->id => ['role' => 'creator']]);
    app(TenantContext::class)->set($organization);
    $question = Question::create(['organization_id' => $organization->id, 'creator_id' => $creator->id, 'type' => 'fill_blank', 'prompt' => 'Sebelum', 'correct_answer' => 'jawab']);
    app(TenantContext::class)->clear();

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->patch(route('questions.update', $question), ['type' => 'fill_blank', 'prompt' => 'Sesudah', 'correct_answer' => 'jawab'])
        ->assertRedirect();
    $this->assertDatabaseHas('questions', ['id' => $question->id, 'prompt' => 'Sesudah']);

    $this->actingAs($otherCreator)->post(route('organizations.switch', $organization));
    $this->delete(route('questions.destroy', $question))->assertForbidden();

    $this->actingAs($creator)->post(route('organizations.switch', $organization));
    $this->delete(route('questions.destroy', $question))->assertRedirect();
    $this->assertDatabaseMissing('questions', ['id' => $question->id]);
});
