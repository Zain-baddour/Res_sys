<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;


class StaffRequestRejectedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */

    protected $hall;
    public function __construct($hall)
    {
        $this->hall = $hall;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database'];
    }


    public function toDatabase($notifiable)
    {
        return [
            'title' => 'job request denied',
            'body' => 'unfortunately, your request to work in a lounge'. $this->hall->name .'has been rejected ',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
