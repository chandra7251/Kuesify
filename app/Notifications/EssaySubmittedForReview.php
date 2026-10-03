<?php

namespace App\Notifications;

use App\Models\QuizAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EssaySubmittedForReview extends Notification
{
    use Queueable;

    public function __construct(private readonly QuizAttempt $attempt) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
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
            'participant_name' => $this->attempt->participant->name,
            'message' => 'Peserta '.$this->attempt->participant->name.' mengirimkan jawaban essay pada kuis '.$this->attempt->quiz->title.' yang membutuhkan penilaian.',
        ];
    }
}
