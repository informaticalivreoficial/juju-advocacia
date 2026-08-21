<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private ?string $token;

    private ?string $chatId;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id');
    }

    public function isConfigured(): bool
    {
        return filled($this->token) && filled($this->chatId);
    }

    public function sendMessage(string $message): bool
    {
        return $this->send('sendMessage', ['text' => $message]);
    }

    public function sendHtml(string $html): bool
    {
        return $this->send('sendMessage', ['text' => $html, 'parse_mode' => 'HTML']);
    }

    private function send(string $method, array $payload): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $response = Http::timeout(15)->post(
                "https://api.telegram.org/bot{$this->token}/{$method}",
                array_merge($payload, ['chat_id' => $this->chatId])
            );

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar mensagem ao Telegram', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
