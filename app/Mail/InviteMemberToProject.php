<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteMemberToProject extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $team;
    protected $project_name;
    protected $document_type;
    protected $link;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $team, $project_name, $document_type, $link)
    {
        $this->user = $user;
        $this->team = $team;
        $this->project_name = $project_name;
        $this->document_type = $document_type;
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
            ->subject('SMVRT Legal: Invited you to the Team.')
            ->markdown('emails.invite-member-to-project', [
                    'user' => $this->user,
                    'team' => $this->team,
                    'project_name' => $this->project_name, 
                    'document_type' => $this->document_type,
                    'link' => $this->link,
                ]);
    }
}
