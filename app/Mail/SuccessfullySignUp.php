<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SuccessfullySignUp extends Mailable
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
            ->subject("SMVRT LEGAL: Please Verify Your Account")
            ->markdown('emails.successfully-signup', ['link' => $this->link, 'email' => $this->email]);
    }
}
