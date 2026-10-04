<?php

namespace App\Notifications;

use App\Models\QuizAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttemptGraded extends Notification
{
    use Queueable;

    public function __construct(private readonly QuizAttempt $attempt) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'attempt_id' => $this->attempt->id,
            'quiz_id' => $this->attempt->quiz_id,
            'quiz_title' => $this->attempt->quiz->title,
            'score' => $this->attempt->score,
            'message' => 'Nilai quiz '.$this->attempt->quiz->title.' sudah tersedia.',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nilai quiz sudah tersedia')
            ->line('Nilai quiz '.$this->attempt->quiz->title.' sudah tersedia.')
            ->line('Skor: '.$this->attempt->score);
    }
}
