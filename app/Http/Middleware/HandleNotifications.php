<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleNotifications
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = null;
        $guard = null;

        $isImpersonating = session()->has('impersonated_by');
        $guardPriority = $isImpersonating ? ['dosen', 'mahasiswa', 'admin'] : ['admin', 'dosen', 'mahasiswa'];

        foreach ($guardPriority as $g) {
            if (auth($g)->check()) {
                $user = auth($g)->user();
                $guard = $g;
                break;
            }
        }

        $notifications = [];
        $count = 0;

        if ($user) {
            $unreadNotifications = $user->unreadNotifications()->orderBy('created_at', 'desc')->limit(5)->get();
            $count = $user->unreadNotifications()->count();
            
            foreach ($unreadNotifications as $notification) {
                $item = [
                    'title' => $this->getNotificationTitle($notification),
                    'message' => $notification->data['message'] ?? 'Notifikasi baru',
                    'url' => $notification->data['action_url'] ?? '#',
                    'created_at' => $notification->created_at,
                    'read_at' => $notification->read_at,
                ];
                $notifications[] = $item;
            }
        }

        view()->share('navbarNotifications', [
            'items' => $notifications,
            'count' => $count,
            'guard' => $guard,
        ]);

        return $next($request);
    }

    private function getNotificationTitle($notification)
    {
        switch ($notification->type) {
            case 'App\Notifications\SuratSubmittedNotification':
                return 'Pengajuan Surat Baru';
            case 'App\Notifications\SuratStatusUpdatedNotification':
                return 'Status Surat Diperbarui';
            case 'App\Notifications\NewSeminarRegistrationNotification':
                return 'Pendaftaran Seminar Baru';
            default:
                return 'Notifikasi';
        }
    }
}
