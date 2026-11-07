<?php

declare(strict_types=1);

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    /** Created by an admin; has not set a password and signed in yet. */
    case Invited = 'invited';
    /** Blocked from signing in. Any open session ends on its next request. */
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Invited => 'Invited',
            self::Suspended => 'Suspended',
        };
    }

    public function canSignIn(): bool
    {
        return $this === self::Active;
    }
}
