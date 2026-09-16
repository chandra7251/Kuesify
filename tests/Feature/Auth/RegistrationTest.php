<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'participant',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/choose-avatar');
});

test('registration creates an organization membership matching chosen role', function () {
    $this->post('/register', [
        'name' => 'Rani Putri',
        'email' => 'rani@example.com',
        'role' => 'creator',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = \App\Models\User::where('email', 'rani@example.com')->firstOrFail();

    $this->assertDatabaseHas('organization_user', ['user_id' => $user->id, 'role' => 'creator']);
    expect(session('organization_id'))->toBeInt();
});

test('registration requires a valid role', function () {
    $response = $this->post('/register', [
        'name' => 'Bad Role User',
        'email' => 'badrole@example.com',
        'role' => 'invalid_role',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('role');
});
