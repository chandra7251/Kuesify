<?php

use App\Models\Organization;
use App\Models\User;

it('lets organization admin add member and create a group', function () {
    $admin = User::factory()->create();
    $participant = User::factory()->create(['email' => 'student@example.com']);
    $organization = Organization::factory()->create();
    $organization->members()->attach($admin, ['role' => 'organization_admin']);
    $this->actingAs($admin)->post(route('organizations.switch', $organization));

    $this->post(route('organization.members.store'), ['email' => 'student@example.com', 'role' => 'participant'])
        ->assertRedirect()->assertSessionHasNoErrors();
    $this->post(route('organization.groups.store'), ['name' => 'Kelas XII IPA 1'])
        ->assertRedirect()->assertSessionHasNoErrors();

    $this->assertDatabaseHas('organization_user', ['organization_id' => $organization->id, 'user_id' => $participant->id, 'role' => 'participant']);
    $this->assertDatabaseHas('groups', ['organization_id' => $organization->id, 'name' => 'Kelas XII IPA 1']);
});

it('blocks creator from organization management', function () {
    $creator = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    $this->post(route('organization.groups.store'), ['name' => 'Tidak boleh'])->assertForbidden();
});
