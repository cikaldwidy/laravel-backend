<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

class WebPushService
{
    public function sendToUser(User $user, array $payload): array
    {
        $subscriptions = $user->pushSubscriptions()->get();

        return $this->sendToSubscriptions($subscriptions, $payload);
    }

    public function sendToSubscriptions(iterable $subscriptions, array $payload): array
    {
        $publicKey = config('services.webpush.public_key');
        $privateKey = config('services.webpush.private_key');
        $subject = config('services.webpush.subject');

        if (blank($publicKey) || blank($privateKey)) {
            return ['sent' => 0, 'failed' => 0, 'expired' => 0, 'error' => 'VAPID keys belum dikonfigurasi.'];
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => $subject,
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ], [
            'TTL' => 3600,
            'urgency' => 'normal',
        ]);

        $sent = 0;
        $failed = 0;
        $expired = 0;
        $encodedPayload = json_encode($payload, JSON_THROW_ON_ERROR);

        foreach ($subscriptions as $pushSubscription) {
            if (!$pushSubscription instanceof PushSubscription) {
                continue;
            }

            try {
                $report = $webPush->sendOneNotification(
                    Subscription::create([
                        'endpoint' => $pushSubscription->endpoint,
                        'publicKey' => $pushSubscription->public_key,
                        'authToken' => $pushSubscription->auth_token,
                        'contentEncoding' => $pushSubscription->content_encoding,
                    ]),
                    $encodedPayload
                );

                if ($report->isSuccess()) {
                    $sent++;
                    $pushSubscription->forceFill(['last_used_at' => now()])->save();
                    continue;
                }

                $failed++;

                if ($report->isSubscriptionExpired()) {
                    $expired++;
                    $pushSubscription->delete();
                }

                Log::warning('Web push notification failed.', [
                    'endpoint' => $pushSubscription->endpoint,
                    'reason' => $report->getReason(),
                ]);
            } catch (Throwable $exception) {
                $failed++;
                Log::warning('Web push notification exception.', [
                    'endpoint' => $pushSubscription->endpoint,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return compact('sent', 'failed', 'expired');
    }
}
