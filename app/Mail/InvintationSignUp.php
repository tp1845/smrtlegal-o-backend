<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvintationSignUp extends Mailable
{
    use Queueable, SerializesModels;

    public $team;
    public $user;
    public $link;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($team, $user, $link)
    {
        $this->team = $team;
        $this->user = $user;
        $this->link = $link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject("SMVRT LEGAL: Sign up by invitation")
            ->markdown('emails.invintation-signup', ['team' => $this->team, 'from' => $this->user, 'link' => $this->link]);
    }
}
