<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Seminar;

class SeminarStatusUpdatedNotification extends Notification
{
    use Queueable;

    public $seminar;
    public $previousStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Seminar $seminar, string $previousStatus)
    {
        $this->seminar = $seminar;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusMessages = [
            'diajukan' => 'Diajukan',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'belum_lengkap' => 'Belum Lengkap',
            'selesai' => 'Selesai',
        ];

        $statusText = $statusMessages[$this->seminar->status] ?? $this->seminar->status;
        $subject = 'Status Seminar Diperbarui: ' . $statusText;

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Halo ' . ($notifiable->nama ?? 'User') . ',')
            ->line('Status pendaftaran seminar Anda telah diperbarui.')
            ->line('**Detail Seminar:**')
            ->line('Judul: ' . $this->seminar->judul)
            ->line('Status Terbaru: ' . $statusText)
            ->action('Lihat Detail', route('mahasiswa.dashboard') . '#seminar-saya')
            ->line('Terima kasih.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusMessages = [
            'diajukan' => 'Diajukan',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'belum_lengkap' => 'Belum Lengkap',
            'selesai' => 'Selesai',
        ];

        $statusText = $statusMessages[$this->seminar->status] ?? $this->seminar->status;

        return [
            'seminar_id' => $this->seminar->id,
            'judul' => $this->seminar->judul,
            'status' => $this->seminar->status,
            'status_text' => $statusText,
            'message' => 'Status seminar diperbarui menjadi: ' . $statusText,
            'action_url' => route('mahasiswa.dashboard') . '#seminar-saya',
        ];
    }
}
