<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class RejectRequestToChangeRole extends Notification
{
    use Queueable;

    protected $team;
    protected $role;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($role, $team)
    {
        $this->role = $role;
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
            'team' => $this->team,
            'message' => "Your request to change your role to <strong>" . $this->role->name . "</strong> has be rejected",
            'confirm' => false,
        ];
    }
}
