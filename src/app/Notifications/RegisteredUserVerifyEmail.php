<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class RegisteredUserVerifyEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verifyURL = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->from('grow@example.com', 'GROW')
            ->subject('【GROW】会員仮登録のお知らせ')
            ->markdown('emails.registered_user_verify_email', [
                'greeting' => '会員仮登録のお知らせ',
                'introLines' => [
                    'この度は、GROWへの新規会員登録をお申し込みいただき、誠にありがとうございます。',
                    '以下のリンクをクリックして、メールアドレスの確認を完了させ、会員登録手続きを進めてください。'
                ],
                'actionText' => 'メールアドレスを確認する',
                'actionUrl' => $verifyURL,
                'outroLines' => [
                    'リンクの有効期限は、送信から1時間です。この期間を過ぎた場合は、再度登録をお願いいたします。',
                    'もし、このメールに心当たりがない場合は、このメールを削除してください。',
                    '今後とも、GROWをどうぞよろしくお願いいたします。'
                ]
            ]);
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

    protected function verificationUrl($notifiable)
    {
        $endpoint = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        return $endpoint;
    }
}
