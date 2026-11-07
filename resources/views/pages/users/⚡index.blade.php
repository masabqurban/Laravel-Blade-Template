<?php

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Livewire\Forms\UserForm;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Users')] class extends Component {
    use WithPagination;

    // Filters live in the query string, so results are shareable and survive a refresh.
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $role = '';

    #[Url(except: '')]
    public string $status = '';

    public UserForm $form;

    #[Locked]
    public ?int $deletingId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    /**
     * Any filter change starts again from the first page.
     */
    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'role', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'role', 'status');
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, User>
     */
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        // Unknown values from a hand-edited URL are ignored, not passed to the query.
        return User::query()
            ->filter([
                'q' => $this->search,
                'role' => Role::tryFrom($this->role),
                'status' => UserStatus::tryFrom($this->status),
            ])
            ->latest()
            ->latest('id')
            ->paginate(10);
    }

    public function create(): void
    {
        $this->authorize('create', User::class);

        $this->form->reset();
        $this->resetValidation();
        Flux::modal('user-form')->show();
    }

    public function edit(User $user): void
    {
        $this->authorize('update', $user);

        $this->resetValidation();
        $this->form->setUser($user);
        Flux::modal('user-form')->show();
    }

    public function save(): void
    {
        $user = $this->form->user;
        $this->authorize($user ? 'update' : 'create', $user ?? User::class);

        $saved = $this->form->save();

        Flux::modal('user-form')->close();
        Flux::toast(variant: 'success', text: $user ? __('Saved :name.', ['name' => $saved->name]) : __('Added :name.', ['name' => $saved->name]));
    }

    public function confirmDelete(User $user): void
    {
        $this->authorize('delete', $user);

        $this->deletingId = $user->id;
        Flux::modal('delete-user')->show();
    }

    public function delete(): void
    {
        $user = User::query()->findOrFail($this->deletingId);
        $this->authorize('delete', $user);

        $user->delete();
        $this->deletingId = null;

        Flux::modal('delete-user')->close();
        Flux::toast(variant: 'success', text: __('Deleted :name.', ['name' => $user->name]));
    }
}; ?>

<section class="flex w-full flex-col gap-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Users') }}</flux:heading>
            <flux:subheading>{{ __('Manage who has access to your workspace.') }}</flux:subheading>
        </div>

        @can('create', App\Models\User::class)
            <flux:button variant="primary" icon="plus" wire:click="create" data-test="add-user">
                {{ __('Add user') }}
            </flux:button>
        @endcan
    </div>

    <div class="flex flex-col gap-3 md:flex-row">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            :placeholder="__('Search by name or email...')"
            :aria-label="__('Search users')"
            clearable
            class="md:flex-1"
        />

        <flux:select wire:model.live="role" :aria-label="__('Filter by role')" class="md:max-w-44">
            <flux:select.option value="">{{ __('All roles') }}</flux:select.option>
            @foreach (Role::cases() as $case)
                <flux:select.option :value="$case->value">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="status" :aria-label="__('Filter by status')" class="md:max-w-44">
            <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
            @foreach (UserStatus::cases() as $case)
                <flux:select.option :value="$case->value">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <flux:table :paginate="$this->users" wire:loading.class="opacity-60">
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Role') }}</flux:table.column>
            <flux:table.column class="max-sm:hidden">{{ __('Status') }}</flux:table.column>
            <flux:table.column class="max-md:hidden">{{ __('Joined') }}</flux:table.column>
            <flux:table.column><span class="sr-only">{{ __('Actions') }}</span></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->users as $user)
                <flux:table.row :key="$user->id">
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:avatar size="sm" :name="$user->name" :initials="$user->initials()" />
                            <div class="min-w-0">
                                <div class="truncate font-medium text-zinc-800 dark:text-white">{{ $user->name }}</div>
                                <div class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</div>
                            </div>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <x-users.role-badge :role="$user->role" />
                    </flux:table.cell>
                    <flux:table.cell class="max-sm:hidden">
                        <x-users.status-badge :status="$user->status" />
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap max-md:hidden">
                        <time datetime="{{ $user->created_at?->toIso8601String() }}">{{ $user->created_at?->format('M j, Y') }}</time>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        @canany(['update', 'delete'], $user)
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" :aria-label="__('Actions for :name', ['name' => $user->name])" />
                                <flux:menu>
                                    @can('update', $user)
                                        <flux:menu.item icon="pencil-square" wire:click="edit({{ $user->id }})">{{ __('Edit') }}</flux:menu.item>
                                    @endcan
                                    @can('delete', $user)
                                        <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $user->id }})">{{ __('Delete') }}</flux:menu.item>
                                    @endcan
                                </flux:menu>
                            </flux:dropdown>
                        @endcanany
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="py-12 text-center">
                        <flux:heading>{{ __('No users found') }}</flux:heading>
                        <flux:text class="mt-1">{{ __('Try a different search or clear the filters.') }}</flux:text>
                        <flux:button size="sm" class="mt-4" wire:click="clearFilters">{{ __('Clear filters') }}</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="user-form" class="w-full max-w-md">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $form->user ? __('Edit user') : __('Add user') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ $form->user ? __('Update this person\'s details and access.') : __('They\'ll set their own password using the reset link.') }}
                </flux:text>
            </div>

            <flux:input wire:model="form.name" :label="__('Full name')" required autocomplete="off" />
            <flux:input wire:model="form.email" :label="__('Email')" type="email" required autocomplete="off" />

            @if ($form->canChangeAccess())
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model="form.role" :label="__('Role')">
                        @foreach (Role::cases() as $case)
                            <flux:select.option :value="$case->value">{{ $case->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="form.status" :label="__('Status')">
                        @foreach (UserStatus::cases() as $case)
                            <flux:select.option :value="$case->value">{{ $case->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            @else
                <flux:callout icon="information-circle" variant="secondary" :text="__('You can\'t change your own role or status.')" />
            @endif

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" data-test="save-user">
                    {{ $form->user ? __('Save changes') : __('Add user') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="delete-user" class="w-full max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete this user?') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Their account and data are removed permanently. This can\'t be undone.') }}</flux:text>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="delete" data-test="confirm-delete">{{ __('Delete user') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</section>
