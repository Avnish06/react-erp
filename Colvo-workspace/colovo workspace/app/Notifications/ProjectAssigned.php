<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectAssigned extends Notification
{
    use Queueable;

    protected $project;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(\App\Models\Project $project)
    {
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
        return ['database'];
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
            'title' => 'New Project Assigned',
            'message' => 'You have been assigned to the project: ' . $this->project->title,
            'url' => route('employee.my-projects.show', $this->project->id),
        ];
    }
}
