<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     */
    public function index(Request $request)
    {
        $user = null;
        $guard = null;

        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            $guard = 'admin';
        } elseif (Auth::guard('dosen')->check()) {
            $user = Auth::guard('dosen')->user();
            $guard = 'dosen';
        } elseif (Auth::guard('mahasiswa')->check()) {
            $user = Auth::guard('mahasiswa')->user();
            $guard = 'mahasiswa';
        }

        if (!$user) {
            return redirect()->route('login');
        }

        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(20);

        if ($guard === 'admin') {
            return view('admin.notifications.index', compact('notifications'));
        } elseif ($guard === 'dosen') {
            return view('dosen.notifications.index', compact('notifications'));
        } else {
            return view('mahasiswa.notifications.index', compact('notifications'));
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAsRead(Request $request)
    {
        $user = null;

        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
        } elseif (Auth::guard('dosen')->check()) {
            $user = Auth::guard('dosen')->user();
        } elseif (Auth::guard('mahasiswa')->check()) {
            $user = Auth::guard('mahasiswa')->user();
        }

        if ($user) {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markNotificationAsRead(Request $request, $notification)
    {
        $user = null;

        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
        } elseif (Auth::guard('dosen')->check()) {
            $user = Auth::guard('dosen')->user();
        } elseif (Auth::guard('mahasiswa')->check()) {
            $user = Auth::guard('mahasiswa')->user();
        }

        if ($user) {
            $notification = $user->notifications()->find($notification);
            if ($notification) {
                $notification->markAsRead();
            }
        }

        return response()->json(['success' => true]);
    }
}
