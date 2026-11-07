<?php

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to sign in', function () {
    $this->get(route('users.index'))->assertRedirect(route('login'));
});

test('every signed-in user can browse the directory, but only admins see actions', function () {
    $viewer = User::factory()->create();
    User::factory()->create(['name' => 'Someone Else']);

    $this->actingAs($viewer)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee('Someone Else')
        ->assertDontSee('data-test="add-user"', escape: false);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('users.index'))
        ->assertSee('data-test="add-user"', escape: false);
});

test('the list filters by search, role and status, keeping them in the URL', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Vic Viewer']);
    User::factory()->editor()->create(['name' => 'Eddie Editor']);
    User::factory()->suspended()->create(['name' => 'Sam Suspended']);

    // Filters are read from the query string on page load…
    $this->actingAs($admin)
        ->get(route('users.index', ['q' => 'edd', 'role' => 'editor']))
        ->assertSee('Eddie Editor')
        ->assertDontSee('Vic Viewer');

    // …and written back to it as they change.
    Livewire::actingAs($admin)
        ->test('pages::users.index')
        ->set('status', 'suspended')
        ->assertSee('Sam Suspended')
        ->assertDontSee('Eddie Editor')
        ->assertSetStrict('status', 'suspended');
});

test('unknown filter values are ignored instead of reaching the query', function () {
    Livewire::withQueryParams(['role' => 'root'])
        ->actingAs(User::factory()->create(['name' => 'Vera Viewer']))
        ->test('pages::users.index')
        ->assertOk()
        ->assertSee('Vera Viewer');
});

test('the list is paginated, and filtering returns to the first page', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(14)->create();

    $component = Livewire::actingAs($admin)->test('pages::users.index');
    expect($component->instance()->users->total())->toBe(15)
        ->and($component->instance()->users->count())->toBe(10);

    $component->call('nextPage')->assertSet('paginators.page', 2)
        ->set('search', 'a')->assertSet('paginators.page', 1);
});

test('admins can add users, who start as invited by default', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('pages::users.index')
        ->call('create')
        ->set('form.name', 'Grace Hopper')
        ->set('form.email', 'grace@example.com')
        ->set('form.role', 'editor')
        ->call('save')
        ->assertHasNoErrors();

    $user = User::query()->where('email', 'grace@example.com')->firstOrFail();
    expect($user->role)->toBe(Role::Editor)
        ->and($user->status)->toBe(UserStatus::Invited);
});

test('adding validates input and rejects duplicate emails', function () {
    $admin = User::factory()->admin()->create(['email' => 'taken@example.com']);

    Livewire::actingAs($admin)
        ->test('pages::users.index')
        ->call('create')
        ->set('form.name', 'X')
        ->set('form.email', 'taken@example.com')
        ->set('form.role', 'root')
        ->call('save')
        ->assertHasErrors(['form.name', 'form.email', 'form.role'])
        ->assertSee('Another account already uses this email.');
});

test('non-admins cannot add, edit or delete users', function (Role $role) {
    $actor = User::factory()->create(['role' => $role]);
    $other = User::factory()->create();

    $page = fn () => Livewire::actingAs($actor)->test('pages::users.index');
    $page()->call('create')->assertForbidden();
    $page()->call('edit', $other->id)->assertForbidden();
    $page()->call('confirmDelete', $other->id)->assertForbidden();

    // Saving without opening the form is checked too.
    $page()->set('form.name', 'Sneaky Person')
        ->set('form.email', 'sneaky@example.com')
        ->call('save')
        ->assertForbidden();

    expect(User::query()->where('email', 'sneaky@example.com')->exists())->toBeFalse()
        ->and($other->fresh())->not->toBeNull();
})->with([Role::Editor, Role::Viewer]);

test('admins can edit other users, including their access', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    Livewire::actingAs($admin)
        ->test('pages::users.index')
        ->call('edit', $user->id)
        ->assertSet('form.email', $user->email)
        ->set('form.name', 'Renamed Person')
        ->set('form.role', 'editor')
        ->set('form.status', 'suspended')
        ->call('save')
        ->assertHasNoErrors();

    expect($user->fresh())
        ->name->toBe('Renamed Person')
        ->role->toBe(Role::Editor)
        ->status->toBe(UserStatus::Suspended);
});

test('admins can edit their own name but not their own role or status', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('pages::users.index')
        ->call('edit', $admin->id)
        ->assertSee("You can't change your own role or status.")
        ->set('form.name', 'New Name')
        ->set('form.role', 'viewer')
        ->set('form.status', 'suspended')
        ->call('save')
        ->assertHasNoErrors();

    expect($admin->fresh())
        ->name->toBe('New Name')
        ->role->toBe(Role::Admin)
        ->status->toBe(UserStatus::Active);
});

test('the user being edited cannot be swapped from the browser', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    expect(fn () => Livewire::actingAs($admin)
        ->test('pages::users.index')
        ->call('edit', $user->id)
        ->set('form.user', $admin->id))
        ->toThrow(Exception::class);
});

test('admins can delete other users but not themselves', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    Livewire::actingAs($admin)
        ->test('pages::users.index')
        ->call('confirmDelete', $user->id)
        ->call('delete')
        ->assertHasNoErrors();

    expect($user->fresh())->toBeNull();

    Livewire::actingAs($admin)
        ->test('pages::users.index')
        ->call('confirmDelete', $admin->id)
        ->assertForbidden();

    expect($admin->fresh())->not->toBeNull();
});
