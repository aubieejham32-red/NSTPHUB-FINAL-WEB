<?php

namespace App\Mail;

use App\Models\Coordinator;
use App\Models\University;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoordinatorAccountMail extends Mailable
{
    use Queueable;
    use SerializesModels;


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        public Coordinator $coordinator,
        public University $university,
        public string $temporaryPassword,
        public string $accessCode,
        public string $loginUrl,
    ) {
        //
    }


    /*
    |--------------------------------------------------------------------------
    | Email Subject
    |--------------------------------------------------------------------------
    */

    public function envelope(): Envelope
    {
        return new Envelope(
            subject:
                'NSTP HUB Coordinator Credentials'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Email Blade View
    |--------------------------------------------------------------------------
    */

    public function content(): Content
    {
        return new Content(
            view:
                'emails.coordinator-account'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Attachments
    |--------------------------------------------------------------------------
    */

    public function attachments(): array
    {
        return [];
    }
}