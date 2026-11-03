<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;

class ForgotPasswordMail extends Mailable
{
    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'NSTP HUB Password Reset Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.forgot-password',
        );
    }
}