<?php

use App\Models\User;

test('suspended and invited users cannot sign in', function (string $state) {
    $user = User::factory()->{$state}()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors(['email' => 'This account is not active.']);

    $this->assertGuest();
})->with(['suspended', 'invited']);

test('a wrong password gives the generic error, not the status', function () {
    $user = User::factory()->suspended()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertSessionHasErrors('email')
        ->assertSessionMissing('errors.email.0', 'This account is not active.');

    $this->assertGuest();
});

test('suspending a signed-in user ends their session on the next request', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('dashboard'))->assertOk();

    $user->update(['status' => 'suspended']);

    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('signing in records the time', function () {
    $user = User::factory()->create(['last_active_at' => null]);

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->last_active_at)->not->toBeNull();
});

test('new registrations get the least privileged role', function () {
    $this->post(route('register.store'), [
        'name' => 'New Person',
        'email' => 'new@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect(User::query()->where('email', 'new@example.com')->firstOrFail())
        ->role->value->toBe('viewer')
        ->status->value->toBe('active');
});
