<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UniversityCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $credentials;

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    public function build()
    {
        return $this
            ->subject('NSTP HUB University Administrator Account')
            ->view('emails.university_credentials')
            ->with([
                'credentials' => $this->credentials,
            ]);
    }
}