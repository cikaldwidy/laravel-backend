<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    public function publicKey(): JsonResponse
    {
        return response()->json([
            'publicKey' => config('services.webpush.public_key'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'contentEncoding' => ['nullable', 'string', 'in:aesgcm,aes128gcm'],
        ]);

        $subscription = PushSubscription::query()->updateOrCreate(
            ['endpoint_hash' => hash('sha256', $validated['endpoint'])],
            [
                'user_id' => Auth::id(),
                'endpoint' => $validated['endpoint'],
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'content_encoding' => $validated['contentEncoding'] ?? 'aes128gcm',
                'user_agent' => $request->userAgent(),
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Notifikasi berhasil diaktifkan.',
            'subscription_id' => $subscription->id,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['nullable', 'string', 'max:2048'],
        ]);

        PushSubscription::query()
            ->where('user_id', Auth::id())
            ->when(
                $validated['endpoint'] ?? null,
                fn ($query, $endpoint) => $query->where('endpoint', $endpoint)
            )
            ->delete();

        return response()->json([
            'message' => 'Notifikasi berhasil dinonaktifkan.',
        ]);
    }

    public function test(Request $request, WebPushService $webPush): JsonResponse
    {
        $result = $webPush->sendToUser($request->user(), [
            'title' => 'Notifikasi Presensi Aktif',
            'body' => 'PWA siap menerima notifikasi presensi.',
            'url' => route('dashboard', [], false),
            'tag' => 'presensi-test',
        ]);

        return response()->json([
            'message' => $result['sent'] > 0
                ? 'Notifikasi test dikirim.'
                : 'Belum ada subscription aktif untuk dikirim.',
            'result' => $result,
        ]);
    }
}
