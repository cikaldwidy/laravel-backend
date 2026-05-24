<?php

namespace App\Services;

use App\Models\PushSubscription;

class AdminPushService
{
    public function __construct(private readonly WebPushService $webPush)
    {
    }

    public function send(array $payload): array
    {
        $subscriptions = PushSubscription::query()
            ->whereHas('user', fn ($user) => $user->where('role', 'admin'))
            ->get();

        return $this->webPush->sendToSubscriptions($subscriptions, $payload);
    }
}
