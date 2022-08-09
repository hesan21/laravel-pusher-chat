<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class PasswordResetNotification extends Notification
{
    use Queueable;

    /** @var string */
    private string $token;

    /** @var User */
    private $user;

    /**
     * Create a new notification instance.
     *
     * @param string $token
     */
    public function __construct($user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $name = $notifiable instanceof User
        ? $notifiable->name
        : 'Recipient';

        return (new MailMessage())
            ->subject($name.' Password Reset Notification')
            ->greeting('Dear '.$name.',')
            ->line('We received a password reset request for your '.config('app.name').' account. To reset your password open the application and enter the following token:')
            ->line('Your password reset token is: ')
            ->line(new HtmlString("<strong>".$this->token."</strong>"))
            ->line( new HtmlString('Thank you for using <strong>'.config('app.name').' </strong>'));
    }
}
