<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('users who are banned globally cannot login and see clear ban message', function () {
    $user = User::factory()->create([
        'is_banned' => true,
        'banned_reason' => 'Melanggar ketentuan komunitas',
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors([
        'email' => 'Akun Anda telah dinonaktifkan/diblokir oleh Super Admin Platform. Alasan: Melanggar ketentuan komunitas. Silakan hubungi pengelola sistem.',
    ]);
});

test('users whose memberships are all disabled cannot login and see clear message', function () {
    $user = User::factory()->create();
    $organization = \App\Models\Organization::factory()->create();
    $organization->members()->attach($user, ['role' => 'participant', 'is_active' => false]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors([
        'email' => 'Akun Anda telah dinonaktifkan oleh administrator organisasi. Silakan hubungi admin sekolah atau organisasi Anda.',
    ]);
});
