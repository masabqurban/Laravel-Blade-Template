@props(['role'])

<flux:badge size="sm" :color="match ($role) {
    App\Enums\Role::Admin => 'indigo',
    App\Enums\Role::Editor => 'sky',
    App\Enums\Role::Viewer => 'zinc',
}">{{ $role->label() }}</flux:badge>
