<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    // Use 'name' instead of 'email' for login
    $response = $this->post('/login', [
        'name' => $user->name, // replace with your actual login field
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/darbs');

});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'name' => $user->name, // replace with your actual login field
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
