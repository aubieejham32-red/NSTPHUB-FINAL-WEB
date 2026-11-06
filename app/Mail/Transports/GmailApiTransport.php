<?php

namespace App\Mail\Transports;

use App\Models\GmailOAuthToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class GmailApiTransport extends AbstractTransport
{
    /**
     * Send an email using the Gmail API over HTTPS.
     */
    protected function doSend(SentMessage $message): void
    {
        $clientId = config('services.gmail_api.client_id');
        $clientSecret = config('services.gmail_api.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            throw new TransportException(
                'Gmail API credentials are missing.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Get the encrypted refresh token from MySQL
        |--------------------------------------------------------------------------
        */

        $storedToken = GmailOAuthToken::query()->first();

        if (!$storedToken || empty($storedToken->refresh_token)) {
            throw new TransportException(
                'Gmail has not been authorized. Connect the Gmail sender account first.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Request an access token from Google
        |--------------------------------------------------------------------------
        */

        $tokenResponse = Http::asForm()
            ->timeout(30)
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $storedToken->refresh_token,
                'grant_type' => 'refresh_token',
            ]);

        if (!$tokenResponse->successful()) {
            Log::error('Gmail API token refresh failed.', [
                'http_status' => $tokenResponse->status(),
                'google_error' => $tokenResponse->json('error'),
            ]);

            throw new TransportException(
                'Gmail API could not refresh its access token. Check Railway logs.'
            );
        }

        $accessToken = $tokenResponse->json('access_token');

        if (!is_string($accessToken) || $accessToken === '') {
            throw new TransportException(
                'Google did not return a valid Gmail access token.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Save a replacement refresh token if Google provides one
        |--------------------------------------------------------------------------
        */

        $newRefreshToken = $tokenResponse->json('refresh_token');

        if (is_string($newRefreshToken) && $newRefreshToken !== '') {
            $storedToken->refresh_token = $newRefreshToken;
            $storedToken->save();
        }

        /*
        |--------------------------------------------------------------------------
        | Prepare the email
        |--------------------------------------------------------------------------
        |
        | Gmail requires an RFC-compliant MIME message encoded
        | using Base64URL.
        |
        */

        $rawMessage = $message->toString();

        $encodedMessage = rtrim(
            strtr(
                base64_encode($rawMessage),
                '+/',
                '-_'
            ),
            '='
        );

        /*
        |--------------------------------------------------------------------------
        | Send the email through Gmail API
        |--------------------------------------------------------------------------
        */

        $sendResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->timeout(30)
            ->post(
                'https://gmail.googleapis.com/gmail/v1/users/me/messages/send',
                [
                    'raw' => $encodedMessage,
                ]
            );

        if (!$sendResponse->successful()) {
            Log::error('Gmail API email sending failed.', [
                'http_status' => $sendResponse->status(),
                'google_error_code' => $sendResponse->json('error.code'),
                'google_error_status' => $sendResponse->json('error.status'),
                'google_error_message' => $sendResponse->json('error.message'),
            ]);

            throw new TransportException(
                'Gmail API could not send the email. Check Railway logs.'
            );
        }
    }

    /**
     * Identify the custom Laravel mail transport.
     */
    public function __toString(): string
    {
        return 'gmail-api';
    }
}