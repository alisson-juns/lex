<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    public function __construct(
        protected GoogleCalendarService $googleService
    ) {
    }

    public function redirect(): RedirectResponse
    {
        $state = bin2hex(random_bytes(16));
        cache()->put('google_oauth_state_' . auth()->id(), $state, now()->addMinutes(10));

        $url = $this->googleService->getAuthUrl($state);

        return redirect($url);
    }

    public function callback(Request $request): RedirectResponse
    {
        $cached = cache()->pull('google_oauth_state_' . auth()->id());

        if (! $cached || $request->get('state') !== $cached) {
            Log::warning('Google OAuth: state inválido para user #' . auth()->id());
            return redirect('/user/calendar-page')->with('error', 'Falha de segurança. Tente novamente.');
        }

        try {
            $this->googleService->handleCallback(auth()->user(), $request->get('code'));
        } catch (\Exception $e) {
            Log::error('Google OAuth callback error: ' . $e->getMessage());
            return redirect('/user/calendar-page')->with('error', 'Erro ao conectar. Tente novamente.');
        }

        return redirect('/user/calendar-page')->with('success', 'Google Calendar conectado!');
    }

    public function disconnect(): RedirectResponse
    {
        auth()->user()->googleToken?->delete();

        return redirect('/user/calendar-page')
            ->with('success', 'Google Calendar desconectado.');
    }

}
