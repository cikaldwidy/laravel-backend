<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\PushSubscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AnnouncementPushService
{
    public function __construct(private readonly WebPushService $webPush)
    {
    }

    public function send(Announcement $announcement, ?string $url = null): array
    {
        if (!$announcement->is_published) {
            return ['sent' => 0, 'failed' => 0, 'expired' => 0];
        }

        $payload = [
            'title' => $announcement->judul,
            'body' => Str::limit(preg_replace('/\s+/', ' ', trim($announcement->isi)), 120),
            'url' => $url ?? $announcement->action_url ?? route('announcements.index', [], false),
            'tag' => 'announcement-' . $announcement->id,
            'renotify' => true,
        ];

        return $this->webPush->sendToSubscriptions(
            $this->subscriptionsFor($announcement)->get(),
            $payload
        );
    }

    private function subscriptionsFor(Announcement $announcement): Builder
    {
        $query = PushSubscription::query();

        if ($announcement->target_type === 'all') {
            return $query->whereHas('user', fn (Builder $user) => $user->where('role', 'user'));
        }

        if ($announcement->target_type === 'unit' && $announcement->unit_id) {
            return $query->whereHas('user.employeeDetail', function (Builder $detail) use ($announcement) {
                $detail->where('unit_id', $announcement->unit_id);
            });
        }

        $userIds = $announcement->users()->pluck('users.id');

        return $query->whereIn('user_id', $userIds);
    }
}
