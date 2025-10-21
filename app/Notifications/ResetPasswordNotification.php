<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Custom Password Reset Notification for Villa Mitre Gym Management System
 *
 * This notification sends a Spanish-language password reset email to users
 * with a custom reset URL pointing to the frontend application.
 *
 * @package App\Notifications
 */
class ResetPasswordNotification extends Notification
{
    /**
     * The password reset token.
     *
     * @var string
     */
    protected string $token;

    /**
     * The user receiving the notification.
     *
     * @var mixed
     */
    protected mixed $user;

    /**
     * Create a new notification instance.
     *
     * @param string $token The password reset token
     * @param mixed $user The user model instance
     */
    public function __construct(#[\SensitiveParameter] string $token, mixed $user)
    {
        $this->token = $token;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $resetUrl = $this->buildResetUrl($notifiable);
        $expirationMinutes = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);
        $userName = $this->user->name ?? 'Usuario';
        $contactEmail = config('mail.contact_email', env('CONTACT_EMAIL', 'soporte@clubvillamitre.com'));
        $contactPhone = config('mail.contact_phone', env('CONTACT_PHONE', '+54 9 11 1234-5678'));

        return (new MailMessage)
            ->subject('Recuperación de Contraseña - Villa Mitre')
            ->greeting("Hola {$userName},")
            ->line('Has recibido este correo porque solicitaste restablecer la contraseña de tu cuenta en el sistema de gimnasio de Club Villa Mitre.')
            ->line('Para crear una nueva contraseña, haz clic en el siguiente botón:')
            ->action('Restablecer Contraseña', $resetUrl)
            ->line("Este enlace de recuperación expirará en {$expirationMinutes} minutos.")
            ->line('**Importante:** Por tu seguridad, no compartas este enlace con nadie.')
            ->line('Si no solicitaste restablecer tu contraseña, ignora este mensaje. Tu cuenta permanecerá segura y no se realizará ningún cambio.')
            ->line('Si tienes algún problema o necesitas ayuda, puedes contactarnos:')
            ->line("📧 Email: {$contactEmail}")
            ->line("📱 Teléfono: {$contactPhone}")
            ->salutation('Saludos cordiales, El equipo de Club Villa Mitre');
    }

    /**
     * Build the password reset URL for the frontend application.
     *
     * @param mixed $notifiable
     * @return string
     */
    protected function buildResetUrl(mixed $notifiable): string
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', env('APP_URL')));
        $email = $notifiable->getEmailForPasswordReset();

        return rtrim($frontendUrl, '/') . "/password-reset?token={$this->token}&email=" . urlencode($email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ];
    }
}
