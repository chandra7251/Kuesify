<?php

declare(strict_types=1);

use App\Models\User;

it('locks login after five failed attempts for the same email and IP', function () {
    $user = User::factory()->create(['password' => bcrypt('correct-password')]);

    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->from(route('login'))->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertRedirect(route('login'));
    }

    $this->from(route('login'))->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertRedirect(route('login'))->assertSessionHasErrors('email');
});

it('limits registration requests from one IP address', function () {
    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->post('/register', [
            'name' => "Creator {$attempt}",
            'email' => "creator{$attempt}@example.test",
            'role' => 'creator',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect();
        $this->post('/logout')->assertRedirect('/');
    }

    $this->post('/register', [
        'name' => 'Blocked Creator',
        'email' => 'blocked@example.test',
        'role' => 'creator',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertTooManyRequests();
});

it('limits password reset link requests from one email address', function () {
    $user = User::factory()->create();

    for ($attempt = 1; $attempt <= 3; $attempt++) {
        $this->post('/forgot-password', ['email' => $user->email])->assertRedirect();
    }

    $this->post('/forgot-password', ['email' => $user->email])
        ->assertRedirect()
        ->assertSessionHasErrors('email');
});
