<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PublishedNewVersion extends Notification
{
    use Queueable;

    protected $user;
    protected $project;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($project, $user)
    {
       $this->user = $user;
       $this->project = $project;
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
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'project' => $this->project,
            'user' => $this->user,
            'message' => "<strong>" . $this->user->getName() . "</strong> published <a href=''>new version</a>",
        ];
    }
}
