<?php

use App\Models\User;

test('new registration redirects to avatar selection before dashboard', function () {
    $response = $this->post('/register', [
        'name' => 'Avatar User',
        'email' => 'avatar-user@example.com',
        'role' => 'creator',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/choose-avatar');
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'avatar-user@example.com', 'avatar_key' => null]);
});

test('authenticated user without avatar can view selection and save a preset', function () {
    $user = User::factory()->unverified()->create(['avatar_key' => null]);

    $this->actingAs($user)
        ->get('/choose-avatar')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Profile/ChooseAvatar'));

    $this->actingAs($user)
        ->post('/choose-avatar', ['avatar_key' => 'book'])
        ->assertRedirect('/dashboard');

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('verification.notice'));

    expect($user->fresh()->avatar_key)->toBe('book');
});

test('avatar selection rejects unknown presets', function () {
    $user = User::factory()->create(['avatar_key' => null]);

    $this->actingAs($user)
        ->from('/choose-avatar')
        ->post('/choose-avatar', ['avatar_key' => 'remote-image'])
        ->assertRedirect('/choose-avatar')
        ->assertSessionHasErrors('avatar_key');
});
