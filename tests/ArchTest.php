<?php

/*
 * Architecture rules, checked on every test run. They keep the codebase
 * consistent as it grows, without relying on code review to catch drift.
 */

arch('no debugging calls are left in the code')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'die'])
    ->not->toBeUsed();

arch('it follows Laravel conventions')->preset()->laravel();

arch('it avoids insecure functions')->preset()->security()->ignoring([
    // Used by the framework-generated seeder and factories for fake data.
    'Database',
]);

arch('enums are backed enums')
    ->expect('App\Enums')
    ->toBeStringBackedEnums();

arch('new application code declares strict types')
    ->expect(['App\Enums', 'App\Policies', 'App\Livewire\Forms', 'App\Support'])
    ->toUseStrictTypes();

arch('controllers do not reach for the request helper or env()')
    ->expect('App\Http\Controllers')
    ->not->toUse(['request', 'env']);
