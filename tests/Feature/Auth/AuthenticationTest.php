<?php

use App\Models\User;
use App\Services\LegacyPortalClient;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $this->artisan('demo:diagram-accounts')->assertSuccessful();
    $user = User::where('email', 'student@uic.edu.ph')->firstOrFail();
    $this->mock(LegacyPortalClient::class)->shouldNotReceive('login', 'loginCoordinator');

    $response = $this->post('/login', [
        'identifier' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $this->artisan('demo:diagram-accounts')->assertSuccessful();
    $user = User::where('email', 'student@uic.edu.ph')->firstOrFail();
    $this->mock(LegacyPortalClient::class)->shouldNotReceive('login', 'loginCoordinator');

    $response = $this->post('/login', [
        'identifier' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('identifier');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

it('routes a full UIC student email to student portal authentication', function () {
    $this->mock(LegacyPortalClient::class, function ($mock) {
        $mock->shouldReceive('login')->once()->with('240000000631', 'portal-password')->andThrow(new RuntimeException('Fixture rejects password'));
        $mock->shouldNotReceive('loginCoordinator');
    });
    $this->post('/login', ['identifier' => 'lfuentes_240000000631@uic.edu.ph', 'password' => 'portal-password'])
        ->assertSessionHasErrors('identifier');
    $this->assertGuest();
});
