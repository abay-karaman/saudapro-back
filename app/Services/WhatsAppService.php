<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function sendMessage(string $phone, string $message): void
    {
        $idInstance = config('greenapi.id_instance');
        $apiToken   = config('greenapi.api_token');

        $url = "https://api.green-api.com/waInstance{$idInstance}/sendMessage/{$apiToken}";
        $chatId = $phone . '@c.us';

        $response = Http::withOptions(['verify' => false])->post($url, [
            'chatId' => $chatId,
            'message' => $message
        ]);

        if (!$response->successful()) {
            Log::error('Ошибка отправки WhatsApp-сообщения', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        }
    }

    /**
     * Отправка конкретно кода подтверждения
     */
    public function sendVerificationCode(string $phone, string $code): void
    {
        $message = "Ваш код подтверждения от Bismo.kz: {$code}";
        $this->sendMessage($phone, $message);
    }
}
