<?php

it('returns 429 with custom message when rate limit is exceeded', function () {
    for ($i = 0; $i < 60; $i++) {
        $this->getJson('/api/v1/brands');
    }

    $response = $this->getJson('/api/v1/brands');
    $response->assertStatus(429)
        ->assertJsonPath('message', 'Too many requests. Please try again later.');
});
