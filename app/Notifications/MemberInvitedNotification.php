<?php

namespace App\Notifications;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberInvitedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Organization $organization,
        public readonly string $role,
        public readonly ?string $temporaryPassword = null
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $roleLabel = match ($this->role) {
            'organization_admin' => 'Admin Organisasi',
            'creator'            => 'Guru / Pengajar (Creator)',
            'participant'        => 'Siswa / Peserta',
            default              => ucfirst(str_replace('_', ' ', $this->role)),
        };

        $mail = (new MailMessage)
            ->subject("Undangan Bergabung ke Organisasi {$this->organization->name} — Kuesify")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Kamu telah didaftarkan sebagai **{$roleLabel}** di organisasi **{$this->organization->name}** pada platform Kuesify.");

        if ($this->temporaryPassword) {
            $mail->line("Akun Kuesify kamu telah dibuat secara otomatis dengan kredensial sementara berikut:")
                ->line("**Email:** {$notifiable->email}")
                ->line("**Password Sementara:** `{$this->temporaryPassword}`")
                ->line("Demi keamanan, sangat disarankan untuk segera mengubah password kamu setelah berhasil masuk ke sistem.");
        } else {
            $mail->line("Karena kamu sudah memiliki akun terdaftar di Kuesify, kamu dapat langsung login menggunakan email dan password yang biasa kamu gunakan.");
        }

        return $mail
            ->action('Masuk ke Kuesify', url('/login'))
            ->line('Jika kamu merasa tidak pernah didaftarkan ke organisasi ini, kamu dapat mengabaikan email ini.');
    }
}
