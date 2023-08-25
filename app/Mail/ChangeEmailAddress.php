<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChangeEmailAddress extends Mailable
{
    use Queueable, SerializesModels;

    protected $link;
    protected $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($link, $email)
    {
        $this->link = $link;
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject("SMVRT LEGAL: Please confirm your email change")
            ->markdown('emails.change-email-address', ['email' => $this->email, 'link' => $this->link]);
    }
}
