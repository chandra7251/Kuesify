<?php

use App\Models\Organization;
use App\Models\User;
use App\Notifications\MemberInvitedNotification;
use Illuminate\Support\Facades\Notification;

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

it('lets organization admin restore and remove members', function () {
    $admin = User::factory()->create();
    $participant = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($admin, ['role' => 'organization_admin']);
    $organization->members()->attach($participant, ['role' => 'participant', 'is_active' => false]);
    $this->actingAs($admin)->post(route('organizations.switch', $organization));

    // Test Restore
    $this->patch(route('organization.members.restore', $participant->id))
        ->assertRedirect()
        ->assertSessionHas('success', 'Anggota berhasil diaktifkan kembali.');

    expect($organization->members()->whereKey($participant->id)->first()->pivot->is_active)->toBe(1);

    // Test Destroy
    $this->delete(route('organization.members.destroy', $participant->id))
        ->assertRedirect()
        ->assertSessionHas('success', 'Anggota berhasil dihapus dari organisasi.');

    expect($organization->members()->whereKey($participant->id)->exists())->toBeFalse();
});

it('sends invitation notification with temporary password when adding a new user', function () {
    Notification::fake();

    $admin = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->members()->attach($admin, ['role' => 'organization_admin']);
    $this->actingAs($admin)->post(route('organizations.switch', $organization));

    $this->post(route('organization.members.store'), ['email' => 'newstudent@example.com', 'role' => 'participant'])
        ->assertRedirect()
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    $newUser = User::where('email', 'newstudent@example.com')->first();
    expect($newUser)->not->toBeNull();

    Notification::assertSentTo($newUser, MemberInvitedNotification::class, function ($notification) {
        return ! empty($notification->temporaryPassword) && $notification->role === 'participant';
    });
});

it('filters out super_admin from organization members response', function () {
    $admin = User::factory()->create();
    $superAdmin = User::factory()->create(['email' => 'super@example.com']);
    $participant = User::factory()->create(['email' => 'student@example.com']);
    $organization = Organization::factory()->create();
    $organization->members()->attach($admin, ['role' => 'organization_admin']);
    $organization->members()->attach($superAdmin, ['role' => 'super_admin']);
    $organization->members()->attach($participant, ['role' => 'participant']);

    $this->actingAs($admin)->post(route('organizations.switch', $organization));

    $response = $this->get('/admin/members')->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/members')
        ->has('members', 2)
        ->where('members.0.email', fn ($email) => $email !== 'super@example.com')
        ->where('members.1.email', fn ($email) => $email !== 'super@example.com')
    );
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
