<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse|Response
    {
        if (! $request->expectsJson()) {
            return Inertia::render('Notifications');
        }

        return response()->json([
            'data' => $request->user()->notifications()->latest()->limit(20)->get(['id', 'type', 'data', 'read_at', 'created_at']),
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->whereKey($notification)->firstOrFail()->markAsRead();

        return back();
    }
}
