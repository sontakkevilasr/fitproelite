<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\Http\Request;

class NotificationLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = NotificationLog::with('recipientUser', 'client', 'trial')
            ->when($request->filled('recipient_type'), fn ($q) => $q->where('recipient_type', $request->string('recipient_type')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.notifications.index', compact('logs'));
    }
}
