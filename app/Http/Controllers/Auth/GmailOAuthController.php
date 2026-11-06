<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\GmailOAuthToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GmailOAuthController extends Controller
{
    private const STATE_KEY = 'gmail_api_oauth_state';

    /**
     * Start Gmail authorization.
     *
     * Only an authenticated Super Administrator can authorize
     * the NSTP HUB sender account.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $this->ensureSetupAllowed();

        $state = Str::random(64);

        $request->session()->put(self::STATE_KEY, $state);

        $parameters = [
            'client_id' => config('services.gmail_api.client_id'),
            'redirect_uri' => config('services.gmail_api.redirect'),
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/gmail.send',
            'access_type' => 'offline',
            'prompt' => 'consent select_account',
            'state' => $state,
        ];

        $authorizationUrl = 'https://accounts.google.com/o/oauth2/v2/auth?'
            . http_build_query(
                $parameters,
                '',
                '&',
                PHP_QUERY_RFC3986
            );

        return redirect()->away($authorizationUrl);
    }

    /**
     * Handle Google's OAuth callback.
     */
    public function callback(Request $request)
    {
        $this->ensureSetupAllowed();

        $expectedState = $request->session()->pull(self::STATE_KEY);

        $receivedState = $request->query('state');

        if (
            !is_string($expectedState) ||
            !is_string($receivedState) ||
            !hash_equals($expectedState, $receivedState)
        ) {
            abort(403, 'Invalid Gmail authorization state.');
        }

        if ($request->filled('error')) {
            return response(
                'Gmail authorization was cancelled or denied.',
                400
            );
        }

        $authorizationCode = $request->query('code');

        if (
            !is_string($authorizationCode) ||
            $authorizationCode === ''
        ) {
            return response(
                'Google did not return an authorization code.',
                400
            );
        }

        $tokenResponse = Http::asForm()
            ->timeout(30)
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.gmail_api.client_id'),
                'client_secret' => config('services.gmail_api.client_secret'),
                'redirect_uri' => config('services.gmail_api.redirect'),
                'grant_type' => 'authorization_code',
                'code' => $authorizationCode,
            ]);

        if (!$tokenResponse->successful()) {
            Log::warning('Gmail OAuth token exchange failed.', [
                'http_status' => $tokenResponse->status(),
                'google_error' => $tokenResponse->json('error'),
            ]);

            return response(
                'Gmail authorization failed. Check your OAuth configuration and Railway logs.',
                502
            );
        }

        $refreshToken = $tokenResponse->json('refresh_token');

        if (
            !is_string($refreshToken) ||
            $refreshToken === ''
        ) {
            return response(
                'Google did not issue a refresh token. Please authorize the account again.',
                422
            );
        }

        // The model encrypts refresh_token automatically.
        // Store only one authorized Gmail sender account.
        $storedToken = GmailOAuthToken::query()->first();

        if (!$storedToken) {
            $storedToken = new GmailOAuthToken();
        }

        $storedToken->refresh_token = $refreshToken;
        $storedToken->authorized_at = now();
        $storedToken->save();

        return response(
            'Gmail authorization successful! Your refresh token has been stored securely. Return to NSTP HUB and disable Gmail setup after testing.',
            200
        )->header('Cache-Control', 'no-store, private');
    }

    /**
     * Prevent unauthorized users from configuring Gmail.
     */
    private function ensureSetupAllowed(): void
    {
        abort_unless(
            (bool) config('services.gmail_api.setup_enabled'),
            404
        );

        abort_unless(
            Auth::guard('superadmin')->check(),
            403
        );

        abort_unless(
            filled(config('services.gmail_api.client_id')) &&
            filled(config('services.gmail_api.client_secret')) &&
            filled(config('services.gmail_api.redirect')),
            500,
            'Gmail API configuration is incomplete.'
        );
    }
}