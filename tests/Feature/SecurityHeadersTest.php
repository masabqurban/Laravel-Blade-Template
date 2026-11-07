<?php

test('responses carry security headers', function () {
    $response = $this->get(route('home'));

    $response->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

test('every script on the page carries the content security policy nonce', function (string $route) {
    $response = $this->get(route($route));

    $policy = (string) $response->headers->get('Content-Security-Policy');
    expect($policy)->toContain("frame-ancestors 'none'")->toContain("object-src 'none'");

    preg_match("/'nonce-([^']+)'/", $policy, $match);
    expect($match[1] ?? null)->not->toBeEmpty();

    // Browsers block any script without the nonce, so check them all:
    // Vite's, Livewire's and Flux's.
    preg_match_all('/<script\b[^>]*>/', (string) $response->getContent(), $scripts);
    expect($scripts[0])->not->toBeEmpty();

    foreach ($scripts[0] as $tag) {
        expect($tag)->toContain('nonce="'.$match[1].'"');
    }
})->with(['home', 'login', 'register']);

test('each response gets a fresh nonce', function () {
    $first = $this->get(route('home'))->headers->get('Content-Security-Policy');
    $second = $this->get(route('home'))->headers->get('Content-Security-Policy');

    expect($first)->not->toBe($second);
});
