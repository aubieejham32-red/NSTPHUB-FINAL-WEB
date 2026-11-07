<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\GmailOAuthToken;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GmailOAuthController extends Controller
{
    private const STATE_KEY = 'gmail_api_oauth_state';
    private const SCOPE = 'https://www.googleapis.com/auth/gmail.send';

    public function redirect(Request $request): RedirectResponse|JsonResponse
    {
        $this->ensureSetupAllowed();

        $clientId = trim((string) config('services.gmail_api.client_id'));
        $redirectUri = trim((string) config('services.gmail_api.redirect'));
        $sender = trim((string) config('mail.from.address'));

        $this->validateOAuthSettings($clientId, $redirectUri, $sender);

        // Available only to the logged-in Super Administrator while setup is enabled.
        if ($request->boolean('check')) {
            return response()->json([
                'status' => 'configuration_valid',
                'client_id_prefix' => explode('-', $clientId, 2)[0],
                'redirect_uri' => $redirectUri,
                'sender' => $sender,
                'scope' => self::SCOPE,
                'session_driver' => config('session.driver'),
                'message' => 'The Laravel settings are valid. Google authorization is not yet confirmed.',
            ])->header('Cache-Control', 'no-store, private');
        }

        $state = Str::random(64);
        $request->session()->put(self::STATE_KEY, $state);
        $request->session()->save();

        $parameters = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'login_hint' => $sender,
            'state' => $state,
        ];

        $authorizationUrl = 'https://accounts.google.com/o/oauth2/v2/auth?'
            . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);

        Log::info('Starting Gmail OAuth authorization.', [
            'redirect_uri' => $redirectUri,
        ]);

        return redirect()->away($authorizationUrl)
            ->header('Cache-Control', 'no-store, private');
    }

    public function callback(Request $request): Response
    {
        $this->ensureSetupAllowed();

        $expectedState = $request->session()->pull(self::STATE_KEY);
        $receivedState = $request->query('state');

        if (
            !is_string($expectedState) ||
            !is_string($receivedState) ||
            !hash_equals($expectedState, $receivedState)
        ) {
            Log::warning('Gmail OAuth state mismatch.');

            return response('Gmail authorization session expired. Start again.', 403);
        }

        if ($request->filled('error')) {
            $error = (string) $request->query('error');

            Log::warning('Gmail OAuth permission denied.', [
                'google_error' => $error,
            ]);

            return response('Google did not authorize Gmail: ' . $error, 400);
        }

        $code = $request->query('code');

        if (!is_string($code) || $code === '') {
            return response('Google did not return an authorization code.', 400);
        }

        try {
            $tokenResponse = Http::asForm()
                ->timeout(30)
                ->post('https://oauth2.googleapis.com/token', [
                    'client_id' => trim((string) config('services.gmail_api.client_id')),
                    'client_secret' => trim((string) config('services.gmail_api.client_secret')),
                    'redirect_uri' => trim((string) config('services.gmail_api.redirect')),
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                ]);
        } catch (ConnectionException $exception) {
            Log::error('Gmail OAuth token service could not be reached.', [
                'exception' => get_class($exception),
            ]);

            return response('Unable to contact Google for Gmail authorization.', 502);
        }

        if (!$tokenResponse->successful()) {
            Log::error('Gmail OAuth token exchange failed.', [
                'http_status' => $tokenResponse->status(),
                'google_error' => $tokenResponse->json('error'),
                'google_error_description' => $tokenResponse->json('error_description'),
            ]);

            return response('Gmail authorization failed. Check Railway logs.', 502);
        }

        $refreshToken = $tokenResponse->json('refresh_token');

        if (!is_string($refreshToken) || $refreshToken === '') {
            return response(
                'Google did not issue a refresh token. Revoke the previous NSTP HUB permission and authorize again.',
                422
            );
        }

        try {
            $storedToken = GmailOAuthToken::query()->first() ?? new GmailOAuthToken();
            // The model must use Laravel's encrypted cast for refresh_token.
            $storedToken->refresh_token = $refreshToken;
            $storedToken->authorized_at = now();
            $storedToken->save();
        } catch (\Throwable $exception) {
            Log::error('Unable to store Gmail OAuth token.', [
                'exception' => get_class($exception),
            ]);

            return response('Gmail was authorized, but saving the token failed. Check Railway MySQL.', 500);
        }

        Log::info('Gmail sender authorization saved.', [
            'token_record_id' => $storedToken->id,
        ]);

        return response(
            'Gmail authorization successful! Return to NSTP HUB and test Send Email.',
            200
        )->header('Cache-Control', 'no-store, private');
    }

    private function validateOAuthSettings(
        string $clientId,
        string $redirectUri,
        string $sender
    ): void {
        if (!preg_match('/^[0-9]+-[A-Za-z0-9_-]+\.apps\.googleusercontent\.com$/D', $clientId)) {
            abort(500, 'Invalid Gmail OAuth client ID in Railway.');
        }

        $expectedUri = rtrim((string) config('app.url'), '/')
            . '/auth/gmail/callback';

        if ($redirectUri !== $expectedUri || !str_starts_with($redirectUri, 'https://')) {
            Log::error('Gmail OAuth callback mismatch.', [
                'configured_redirect_uri' => $redirectUri,
                'expected_redirect_uri' => $expectedUri,
            ]);

            abort(500, 'Gmail OAuth redirect URI does not match production APP_URL.');
        }

        if (!filter_var($sender, FILTER_VALIDATE_EMAIL)) {
            abort(500, 'MAIL_FROM_ADDRESS must be a valid email address.');
        }
    }

    private function ensureSetupAllowed(): void
    {
        abort_unless((bool) config('services.gmail_api.setup_enabled'), 404);
        abort_unless(Auth::guard('superadmin')->check(), 403);

        abort_unless(
            filled(config('services.gmail_api.client_id')) &&
            filled(config('services.gmail_api.client_secret')) &&
            filled(config('services.gmail_api.redirect')),
            500,
            'Gmail API configuration is incomplete.'
        );
    }
}
