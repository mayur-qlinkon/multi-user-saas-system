<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = $user->notifications();

        // ── Search inside JSON 'data' column ──
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('data->title', 'like', "%{$searchTerm}%")
                  ->orWhere('data->message', 'like', "%{$searchTerm}%");
            });
        }

        // ── Filter by notification type ──
        if ($request->filled('type')) {
            $query->where('data->type', $request->type);
        }

        $notifications = $query->latest()->paginate(20)->withQueryString();

        return view('admin.notifications.index', compact('notifications'));
    }
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications->firstWhere('id', $id);
        $notification?->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }
}