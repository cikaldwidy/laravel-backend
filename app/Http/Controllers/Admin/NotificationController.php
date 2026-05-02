<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminNotificationService;

class NotificationController extends Controller
{
    public function index(AdminNotificationService $notifications)
    {
        return view('admin.notifications.index', [
            'notifications' => $notifications->items(50),
            'notificationCount' => $notifications->count(),
        ]);
    }
}
