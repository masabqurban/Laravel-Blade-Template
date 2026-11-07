@props(['status'])

<flux:badge size="sm" variant="pill" :color="match ($status) {
    App\Enums\UserStatus::Active => 'green',
    App\Enums\UserStatus::Invited => 'amber',
    App\Enums\UserStatus::Suspended => 'red',
}">{{ $status->label() }}</flux:badge>
