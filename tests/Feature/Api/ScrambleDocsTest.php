<?php

it('serves the OpenAPI spec at /docs/api.json', function () {
    $response = $this->get('/docs/api.json');

    $response->assertOk()
        ->assertJsonStructure([
            'openapi',
            'info' => ['title', 'version'],
            'paths',
        ]);
});

it('includes all API endpoints in the spec', function () {
    $spec = $this->get('/docs/api.json')->json();

    expect(count($spec['paths']))->toBeGreaterThanOrEqual(6);
});
