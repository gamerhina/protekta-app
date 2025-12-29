<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\Seminar;

class NewSeminarRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $seminar;

    /**
     * Create a new notification instance.
     */
    public function __construct(Seminar $seminar)
    {
        $this->seminar = $seminar;
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
        return (new MailMessage)
            ->subject('Pendaftaran Seminar Baru - ' . $this->seminar->judul)
            ->greeting('Halo Admin,')
            ->line('Mahasiswa baru telah mendaftar untuk seminar dan menunggu persetujuan Anda.')
            ->line('**Detail Seminar:**')
            ->line('Judul: ' . $this->seminar->judul)
            ->line('Mahasiswa: ' . ($this->seminar->mahasiswa->nama ?? 'N/A'))
            ->line('Tanggal: ' . ($this->seminar->tanggal ? $this->seminar->tanggal->format('d M Y') : 'N/A'))
            ->line('Lokasi: ' . $this->seminar->lokasi)
            ->action('Lihat Pendaftaran', route('admin.seminar.index') . '?filter=diajukan')
            ->line('Mohon segera melakukan review dan approval pendaftaran ini.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'seminar_id' => $this->seminar->id,
            'judul' => $this->seminar->judul,
            'mahasiswa' => $this->seminar->mahasiswa->nama ?? null,
            'tanggal' => $this->seminar->tanggal,
            'status' => 'diajukan',
        ];
    }
}
