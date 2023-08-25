<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
 

class RequestsToChangeRole extends Notification
{
    use Queueable;

    protected $role;
    protected $from;
    protected $team;
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($role, $from, $team)
    {
        $this->role = $role;
        $this->from = $from;
        $this->team = $team;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray(object $notifiable)
    {
        return [
            'role' => $this->role,
            'from' => $this->from,
            'team' => $this->team,
            'message' => "<strong>" . ($this->from->fname  ? $this->from->fname . ' ' . $this->from->lname : $this->from->email) . 
             "</strong> asked to change his role to <strong>" . $this->role->name . "</strong>",
            'confirm' => true,
        ];
    }
}
