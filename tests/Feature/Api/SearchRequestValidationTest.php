<?php

it('returns 422 when source is unknown', function () {
    $response = $this->getJson('/api/v1/search?q=toyota&source=xyz');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('source');
});

it('accepts source=infoauto_v2', function () {
    $response = $this->getJson('/api/v1/search?q=toyota&source=infoauto_v2');

    $response->assertOk();
});

it('still accepts source=infoauto for backwards compatibility', function () {
    $response = $this->getJson('/api/v1/search?q=toyota&source=infoauto');

    $response->assertOk();
});

it('still accepts source=cca', function () {
    $response = $this->getJson('/api/v1/search?q=toyota&source=cca');

    $response->assertOk();
});

it('still accepts source=acara', function () {
    $response = $this->getJson('/api/v1/search?q=toyota&source=acara');

    $response->assertOk();
});
