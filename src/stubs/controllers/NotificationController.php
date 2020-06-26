<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::latest()->paginate();
        return view('models.notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        $notification->update(['read' => 1]);
        return 'done';
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return 'done';
    }

}
