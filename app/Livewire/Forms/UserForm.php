<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

/**
 * Create and edit form for the users page. Authorisation is checked by the
 * page before every action; this object validates and persists.
 */
class UserForm extends Form
{
    /** The user being edited, or null when creating. The browser can't change it. */
    #[Locked]
    public ?User $user = null;

    public string $name = '';

    public string $email = '';

    public string $role = 'viewer';

    public string $status = 'invited';

    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->status = $user->status->value;
    }

    /**
     * Whether the role and status fields apply: admins can't change their own.
     */
    public function canChangeAccess(): bool
    {
        return $this->user === null || Gate::allows('changeAccess', $this->user);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique(User::class)->ignore($this->user),
            ],
            'role' => ['required', Rule::enum(Role::class)],
            'status' => ['required', Rule::enum(UserStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'email.unique' => __('Another account already uses this email.'),
        ];
    }

    public function save(): User
    {
        /** @var array{name: string, email: string, role: string, status: string} $validated */
        $validated = $this->validate();

        if (! $this->canChangeAccess()) {
            unset($validated['role'], $validated['status']);
        }

        if ($this->user === null) {
            // New users set their own password through the reset flow.
            $user = User::query()->create([...$validated, 'password' => Str::password(32)]);
        } else {
            $user = $this->user;
            $user->update($validated);
        }

        $this->reset();

        return $user;
    }
}
