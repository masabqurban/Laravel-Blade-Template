<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * PLACEHOLDER DATA for development and demos: every name and email is
 * generated and fictional. Never run it against a production database.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public const DEMO_EMAIL = 'demo@example.com';

    public function run(): void
    {
        // Safe to run on every container start: seed only once.
        if (User::query()->where('email', self::DEMO_EMAIL)->exists()) {
            $this->command?->info('Demo data already present, skipping.');

            return;
        }

        User::factory()->admin()->create([
            'name' => 'Demo Admin',
            'email' => self::DEMO_EMAIL,
            'created_at' => now()->subDays(200),
            'last_active_at' => now(),
        ]);

        $roles = [Role::Viewer, Role::Editor, Role::Viewer, Role::Admin, Role::Viewer, Role::Editor];
        $statuses = [UserStatus::Active, UserStatus::Active, UserStatus::Active, UserStatus::Invited, UserStatus::Active, UserStatus::Suspended];

        // Spread over the last ~6 months, so the dashboard chart has data.
        foreach (range(0, 46) as $index) {
            $status = $statuses[$index % count($statuses)];

            User::factory()
                ->state([
                    'role' => $roles[$index % count($roles)],
                    'status' => $status,
                    'email' => fake()->unique()->userName().'@example.com',
                    'email_verified_at' => $status === UserStatus::Invited ? null : now(),
                    'created_at' => now()->subHours((int) round(($index * 3.7 + 1) * 24)),
                    'last_active_at' => $status === UserStatus::Invited ? null : now()->subDays($index % 9),
                ])
                ->create();
        }
    }
}
