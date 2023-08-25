<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Invite3PartyCollaborate extends Mailable
{
    use Queueable, SerializesModels;

    protected $company_name;
    protected $project_name;
    protected $document_type;
    protected $due_date;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($company_name, $project_name, $document_type, $due_date)
    {
        $this->company_name = $company_name;
        $this->project_name = $project_name;
        $this->document_type = $document_type;
        $this->due_date = $due_date;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject("SMVRT Legal: " . $this->company_name .  " has invited you join the “" . $this->project_name .  "” project (" . $this->document_type . ")")    
            ->markdown('emails.invite-3part-collaborate', [
                                                            'company_name' => $this->company_name,
                                                            'project_name' => $this->project_name, 
                                                            'document_type' => $this->document_type,
                                                            'due_date' => $this->due_date
                                                        ]);
    }
}
