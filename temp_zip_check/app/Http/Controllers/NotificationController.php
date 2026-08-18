<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->take(10)->get();
        $unreadCount = $request->user()->unreadNotifications()->count();
        
        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }
}
