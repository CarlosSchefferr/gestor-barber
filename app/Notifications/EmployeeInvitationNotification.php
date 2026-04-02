<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\User;

class EmployeeInvitationNotification extends Notification
{
    use Queueable;

    public $password;

    /**
     * Create a notification instance.
     */
    public function __construct(string $password)
    {
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $loginUrl = url(route('login', [], false));

        return (new MailMessage)
            ->subject('Bem-vindo ao ' . config('app.name') . ' - Suas credenciais de acesso')
            ->markdown('emails.employee-invitation', [
                'user' => $notifiable,
                'password' => $this->password,
                'loginUrl' => $loginUrl,
            ]);
    }
}
