<?php

it('returns 422 when source is unknown', function () {
    $response = $this->getJson('/api/v1/search?q=toyota&source=xyz');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('source');
});

it('returns 422 when source=infoauto_v2 (alias removed in Fase 4a)', function () {
    $response = $this->getJson('/api/v1/search?q=toyota&source=infoauto_v2');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('source');
});

it('accepts source=infoauto (read model)', function () {
    $response = $this->getJson('/api/v1/search?q=toyota&source=infoauto');

    $response->assertOk();
});

it('accepts source=cca', function () {
    $response = $this->getJson('/api/v1/search?q=toyota&source=cca');

    $response->assertOk();
});

it('accepts source=acara', function () {
    $response = $this->getJson('/api/v1/search?q=toyota&source=acara');

    $response->assertOk();
});
