<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * What a user may do. Authorization checks go through policies, which read
 * these roles, so changing who can do what happens in one place.
 */
enum Role: string
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Editor => 'Editor',
            self::Viewer => 'Viewer',
        };
    }
}
