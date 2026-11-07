<?php

use App\Models\User;
use App\Support\DashboardMetrics;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('the dashboard renders its shell and loads the metrics lazily', function () {
    $this->actingAs(User::factory()->create(['name' => 'Ada Lovelace']))
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Welcome back, Ada.')
        // The first response carries the placeholder, not the numbers.
        ->assertSee('Loading metrics…')
        ->assertDontSee('vs last 30 days');
});

test('the metrics show stats, sign-ups and recent users', function () {
    $this->travelTo(now()->setDate(2026, 6, 15));
    $user = User::factory()->create(['name' => 'Newest Person']);
    User::factory()->create(['created_at' => now()->subDays(40)]);
    User::factory()->invited()->create(['created_at' => now()->subDays(70)]);

    Livewire::actingAs($user)
        ->test('dashboard-metrics')
        ->assertSee(['Total users', 'Pending invites', 'Newest Person'])
        ->assertSeeHtml('+50.0%')
        ->assertSee('Jun');

    $metrics = new DashboardMetrics;
    expect($metrics->stats()[0])->toBe(['key' => 'users', 'label' => 'Total users', 'value' => 3, 'change' => 0.5])
        ->and($metrics->stats()[3]['value'])->toBe(1)
        ->and($metrics->signups())->toHaveCount(6)
        ->and($metrics->signups()[5])->toBe(['month' => 'Jun', 'count' => 1]);
});

test('change ratios handle growth, decline and an empty baseline', function () {
    expect(DashboardMetrics::change(12, 10))->toBe(0.2)
        ->and(DashboardMetrics::change(9, 10))->toBe(-0.1)
        ->and(DashboardMetrics::change(0, 0))->toBe(0.0)
        ->and(DashboardMetrics::change(5, 0))->toBe(1.0);
});
