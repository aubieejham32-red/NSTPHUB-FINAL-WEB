<?php

namespace App\Mail;

use App\Models\Instructor;
use App\Models\University;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InstructorAccountMail extends Mailable
{
    use Queueable;
    use SerializesModels;


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        public Instructor $instructor,
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
                'NSTP HUB Instructor Credentials'
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
                'emails.instructor-account'
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