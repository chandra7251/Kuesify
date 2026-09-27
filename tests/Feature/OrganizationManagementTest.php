<?php

use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use App\Support\TenantContext;

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

it('lets organization admin assign and remove members from groups', function () {
    $organization = Organization::factory()->create();
    $admin = User::factory()->create();
    $participant = User::factory()->create();
    $organization->members()->attach([
        $admin->id => ['role' => 'organization_admin'],
        $participant->id => ['role' => 'participant'],
    ]);

    app(TenantContext::class)->set($organization);
    $group = Group::create(['organization_id' => $organization->id, 'name' => 'Kelas XI']);
    app(TenantContext::class)->clear();

    $this->actingAs($admin)->post(route('organizations.switch', $organization));
    $this->post(route('organization.groups.members.store', $group), ['user_id' => $participant->id])->assertRedirect();
    $this->assertDatabaseHas('group_user', ['group_id' => $group->id, 'user_id' => $participant->id]);

    $this->delete(route('organization.groups.members.destroy', [$group, $participant]))->assertRedirect();
    $this->assertDatabaseMissing('group_user', ['group_id' => $group->id, 'user_id' => $participant->id]);
});
