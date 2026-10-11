<?php

use App\Models\Organization;
use App\Models\User;

it('lets organization admin manage members only', function () {
    $admin = User::factory()->create();
    $participant = User::factory()->create(['email' => 'student@example.com']);
    $organization = Organization::factory()->create();
    $organization->members()->attach($admin, ['role' => 'organization_admin']);
    $this->actingAs($admin)->post(route('organizations.switch', $organization));

    $this->post(route('organization.members.store'), ['email' => 'student@example.com', 'role' => 'participant'])
        ->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('success');

    $this->assertDatabaseHas('organization_user', [
        'organization_id' => $organization->id,
        'user_id' => $participant->id,
        'role' => 'participant',
    ]);

    $this->get('/admin/members')->assertOk();
    $this->get('/admin/settings')->assertOk();
    $this->get('/admin/groups')->assertNotFound();
});

it('blocks creator from organization management', function () {
    $creator = User::factory()->create();
    $participant = User::factory()->create(['email' => 'student@example.com']);
    $organization = Organization::factory()->create();
    $organization->members()->attach($creator, ['role' => 'creator']);
    $this->actingAs($creator)->post(route('organizations.switch', $organization));

    $this->post(route('organization.members.store'), ['email' => $participant->email, 'role' => 'participant'])->assertForbidden();
    $this->get('/admin/members')->assertForbidden();
});

it('lets organization admin update organization name and timezone', function () {
    $admin = User::factory()->create();
    $organization = Organization::factory()->create(['name' => 'Old School Name']);
    $organization->members()->attach($admin, ['role' => 'organization_admin']);
    $this->actingAs($admin)->post(route('organizations.switch', $organization));

    $this->patch(route('organization.update'), [
        'name' => 'SMA Negeri 1 Jakarta',
        'timezone' => 'Asia/Jakarta',
    ])->assertRedirect()->assertSessionHas('success');

    expect($organization->fresh()->name)->toBe('SMA Negeri 1 Jakarta');
});

it('blocks non-admin from updating organization details', function () {
    $student = User::factory()->create();
    $organization = Organization::factory()->create(['name' => 'Old School Name']);
    $organization->members()->attach($student, ['role' => 'participant']);
    $this->actingAs($student)->post(route('organizations.switch', $organization));

    $this->patch(route('organization.update'), [
        'name' => 'Hacked Name',
    ])->assertForbidden();
});
