<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\UserStatus;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Aggregates for the dashboard. Every query is portable across SQLite,
 * PostgreSQL and MySQL.
 */
final class DashboardMetrics
{
    /**
     * @return list<array{key: string, label: string, value: int, change: float}>
     */
    public function stats(): array
    {
        $monthAgo = CarbonImmutable::now()->subDays(30);
        $twoMonthsAgo = $monthAgo->subDays(30);

        $total = User::query()->count();
        $newThisMonth = User::query()->where('created_at', '>=', $monthAgo)->count();
        $newLastMonth = User::query()->whereBetween('created_at', [$twoMonthsAgo, $monthAgo])->count();
        $active = User::query()->where('status', UserStatus::Active)->count();
        $invited = User::query()->where('status', UserStatus::Invited)->count();
        // Without status history, compare with accounts that already existed
        // 30 days ago, by their current status. Record status changes to be exact.
        $activeBefore = User::query()->where('status', UserStatus::Active)->where('created_at', '<', $monthAgo)->count();
        $invitedBefore = User::query()->where('status', UserStatus::Invited)->where('created_at', '<', $monthAgo)->count();

        return [
            ['key' => 'users', 'label' => 'Total users', 'value' => $total, 'change' => self::change($total, $total - $newThisMonth)],
            ['key' => 'new', 'label' => 'New this month', 'value' => $newThisMonth, 'change' => self::change($newThisMonth, $newLastMonth)],
            ['key' => 'active', 'label' => 'Active users', 'value' => $active, 'change' => self::change($active, $activeBefore)],
            ['key' => 'pending', 'label' => 'Pending invites', 'value' => $invited, 'change' => self::change($invited, $invitedBefore)],
        ];
    }

    /**
     * New sign-ups per calendar month, oldest first, ending with this month.
     * One query per month keeps it portable across every database driver.
     *
     * @return list<array{month: string, count: int}>
     */
    public function signups(): array
    {
        $start = CarbonImmutable::now()->startOfMonth()->subMonths(5);

        return array_map(function (int $offset) use ($start): array {
            $month = $start->addMonths($offset);

            return [
                'month' => $month->format('M'),
                'count' => User::query()->whereBetween('created_at', [$month, $month->endOfMonth()])->count(),
            ];
        }, range(0, 5));
    }

    /** Change as a ratio: 0.12 = +12%. */
    public static function change(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current === 0 ? 0.0 : 1.0;
        }

        return round(($current - $previous) / $previous, 3);
    }
}
