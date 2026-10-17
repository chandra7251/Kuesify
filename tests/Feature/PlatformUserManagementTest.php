<?php

use App\Models\Organization;
use App\Models\User;

it('lets super admin view platform users list', function () {
    $superAdmin = User::factory()->create();
    $org = Organization::factory()->create();
    $org->members()->attach($superAdmin, ['role' => 'super_admin']);

    $user = User::factory()->create(['name' => 'John Doe']);
    $org->members()->attach($user, ['role' => 'participant']);

    $this->actingAs($superAdmin)
        ->get('/superadmin/users')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/Users')
            ->has('users.data')
            ->where('users.total', 2)
        );
});

it('lets super admin ban and unban a regular user', function () {
    $superAdmin = User::factory()->create();
    $org = Organization::factory()->create();
    $org->members()->attach($superAdmin, ['role' => 'super_admin']);

    $targetUser = User::factory()->create(['name' => 'Bad User', 'is_banned' => false]);
    $org->members()->attach($targetUser, ['role' => 'participant']);

    // Ban
    $this->actingAs($superAdmin)
        ->patch("/superadmin/users/{$targetUser->id}/ban", ['reason' => 'Spamming content'])
        ->assertRedirect()
        ->assertSessionHas('status');

    $targetUser->refresh();
    expect($targetUser->is_banned)->toBeTrue();
    expect($targetUser->banned_reason)->toBe('Spamming content');
    expect($targetUser->banned_at)->not->toBeNull();

    // Unban
    $this->actingAs($superAdmin)
        ->patch("/superadmin/users/{$targetUser->id}/ban")
        ->assertRedirect()
        ->assertSessionHas('status');

    $targetUser->refresh();
    expect($targetUser->is_banned)->toBeFalse();
    expect($targetUser->banned_reason)->toBeNull();
});

it('blocks super admin from banning themselves or other super admins', function () {
    $superAdmin = User::factory()->create();
    $otherSuperAdmin = User::factory()->create();
    $org = Organization::factory()->create();
    $org->members()->attach($superAdmin, ['role' => 'super_admin']);
    $org->members()->attach($otherSuperAdmin, ['role' => 'super_admin']);

    // Cannot ban self
    $this->actingAs($superAdmin)
        ->patch("/superadmin/users/{$superAdmin->id}/ban")
        ->assertStatus(422);

    // Cannot ban another super admin
    $this->actingAs($superAdmin)
        ->patch("/superadmin/users/{$otherSuperAdmin->id}/ban")
        ->assertForbidden();
});

it('lets super admin permanently delete a non-super-admin user', function () {
    $superAdmin = User::factory()->create();
    $targetUser = User::factory()->create();
    $org = Organization::factory()->create();
    $org->members()->attach($superAdmin, ['role' => 'super_admin']);
    $org->members()->attach($targetUser, ['role' => 'participant']);

    $this->actingAs($superAdmin)
        ->delete("/superadmin/users/{$targetUser->id}")
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(User::whereKey($targetUser->id)->exists())->toBeFalse();
});
