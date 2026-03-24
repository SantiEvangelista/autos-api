<?php

it('returns rate limit headers on every API response', function () {
    $response = $this->getJson('/api/v1/brands');

    $response->assertStatus(200)
        ->assertHeader('X-RateLimit-Limit', 60)
        ->assertHeader('X-RateLimit-Remaining', 59);
});

it('decrements remaining count with each request', function () {
    $this->getJson('/api/v1/brands');
    $this->getJson('/api/v1/brands');
    $response = $this->getJson('/api/v1/brands');

    $response->assertStatus(200)
        ->assertHeader('X-RateLimit-Limit', 60)
        ->assertHeader('X-RateLimit-Remaining', 57);
});

it('returns 429 with retry-after and custom message when rate limit is exceeded', function () {
    for ($i = 0; $i < 60; $i++) {
        $this->getJson('/api/v1/brands');
    }

    $response = $this->getJson('/api/v1/brands');

    $response->assertStatus(429)
        ->assertJsonPath('message', 'Too many requests. Please try again later.')
        ->assertHeader('Retry-After')
        ->assertHeader('X-RateLimit-Limit', 60)
        ->assertHeader('X-RateLimit-Remaining', 0);
});
