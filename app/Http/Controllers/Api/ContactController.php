<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // Honeypot: if the hidden field is filled, it's a bot
        if ($request->filled('website')) {
            // Return 200 so the bot thinks it succeeded
            return response()->json(['message' => 'Mensaje enviado.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|indisposable',
            'message' => 'required|string|max:2000',
            'cf_turnstile_response' => 'required|string',
        ], [
            'email.indisposable' => 'No se permiten emails temporales o descartables.',
        ]);

        // Verify Turnstile token
        $turnstileResponse = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('app.turnstile_secret_key'),
            'response' => $validated['cf_turnstile_response'],
            'remoteip' => $request->ip(),
        ]);

        if (! $turnstileResponse->json('success')) {
            return response()->json(['message' => 'Verificación de seguridad fallida. Intentá de nuevo.'], 422);
        }

        $body = "Nombre: {$validated['name']}\n"
            ."Email: {$validated['email']}\n\n"
            .$validated['message'];

        $recipient = config('app.contact_recipient') ?: config('mail.from.address');

        Mail::raw($body, function ($mail) use ($validated, $recipient) {
            $mail->to($recipient)
                ->replyTo($validated['email'], $validated['name'])
                ->subject("Contacto ArgAutos: {$validated['name']}");
        });

        Log::info('Contact form submitted', ['name' => $validated['name'], 'email' => $validated['email']]);

        return response()->json(['message' => 'Mensaje enviado correctamente.']);
    }
}
