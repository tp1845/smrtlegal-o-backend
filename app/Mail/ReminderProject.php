<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderProject extends Mailable
{
    use Queueable, SerializesModels;

    protected $project_name;
    protected $due_date;
    protected $document_type;
    protected $team;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($project_name, $due_date, $document_type, $team)
    {
        $this->project_name = $project_name;
        $this->due_date = $due_date;
        $this->document_type = $document_type;
        $this->team = $team;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('SMVRT Legal: You have tasks for the ' . $this->project_name . ' (' . $this->document_type . ') project due soon!')
            ->markdown('emails.reminder', [
                'project_name' => $this->project_name,
                'due_date' => $this->due_date,
                'document_type' => $this->document_type,
                'team' => $this->team,
            ]);
    }
}
