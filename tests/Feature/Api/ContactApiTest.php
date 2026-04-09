<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

function validContactPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Juan Pérez',
        'email' => 'juan@example.com',
        'message' => 'Hola, quiero saber más sobre la API.',
        'cf_turnstile_response' => 'fake-token',
    ], $overrides);
}

function fakeTurnstileSuccess(): void
{
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response(['success' => true]),
    ]);
}

function fakeTurnstileFailure(): void
{
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response(['success' => false]),
    ]);
}

it('sends a contact message successfully', function () {
    fakeTurnstileSuccess();

    $this->postJson('/contact', validContactPayload())
        ->assertOk()
        ->assertJsonPath('message', 'Mensaje enviado correctamente.');

    Http::assertSent(fn ($r) => $r->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
});

it('silently discards honeypot submissions', function () {
    Http::fake();

    $this->postJson('/contact', validContactPayload([
        'website' => 'https://spam.com',
    ]))
        ->assertOk()
        ->assertJsonPath('message', 'Mensaje enviado.');

    Http::assertNothingSent();
});

it('rejects when turnstile verification fails', function () {
    fakeTurnstileFailure();

    $this->postJson('/contact', validContactPayload())
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Verificación de seguridad fallida. Intentá de nuevo.');
});

it('validates name is required', function () {
    $this->postJson('/contact', validContactPayload(['name' => '']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('validates email is required', function () {
    $this->postJson('/contact', validContactPayload(['email' => '']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('validates email format', function () {
    $this->postJson('/contact', validContactPayload(['email' => 'not-an-email']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('validates message is required', function () {
    $this->postJson('/contact', validContactPayload(['message' => '']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('message');
});

it('validates turnstile response is required', function () {
    $this->postJson('/contact', validContactPayload(['cf_turnstile_response' => '']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('cf_turnstile_response');
});

it('validates name max length', function () {
    $this->postJson('/contact', validContactPayload(['name' => str_repeat('a', 101)]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('validates message max length', function () {
    $this->postJson('/contact', validContactPayload(['message' => str_repeat('a', 2001)]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('message');
});

it('sends turnstile token to cloudflare for verification', function () {
    fakeTurnstileSuccess();

    $this->postJson('/contact', validContactPayload());

    Http::assertSent(function ($request) {
        return $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
            && $request['response'] === 'fake-token';
    });
});

it('rejects disposable email addresses', function () {
    $this->postJson('/contact', validContactPayload(['email' => 'test@mailinator.com']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('is rate limited', function () {
    fakeTurnstileSuccess();

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/contact', validContactPayload())->assertOk();
    }

    $this->postJson('/contact', validContactPayload())
        ->assertStatus(429);
});
