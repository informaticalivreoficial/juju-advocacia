<?php

namespace App\Notifications\Channels;

use App\Services\TelegramService;
use Illuminate\Notifications\Notification;
use SplObjectStorage;

class TelegramChannel
{
    private static ?SplObjectStorage $sent = null;

    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toTelegram')) {
            return;
        }

        self::$sent ??= new SplObjectStorage;

        if (self::$sent->contains($notification)) {
            return;
        }

        self::$sent->attach($notification);

        $message = $notification->toTelegram($notifiable);

        if (blank($message)) {
            return;
        }

        app(TelegramService::class)->sendHtml($message);
    }
}
