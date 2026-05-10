<?php

declare(strict_types=1);

namespace App\Services\Telegram;

use App\Services\Telegram\Contracts\TelegramClientInterface;
use App\Services\Telegram\Exceptions\TelegramApiException;
use App\Services\Telegram\Messages\TelegramMessage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;

final class TelegramClient implements TelegramClientInterface
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly string $apiUrl,
        private readonly string $botToken,
        private readonly int $timeout = 10,
    ) {
    }

    public function send(TelegramMessage $message): int
    {
        if ($this->botToken === '') {
            throw new TelegramApiException('Telegram bot token is not configured.');
        }

        $endpoint = sprintf('%s/bot%s/sendMessage', rtrim($this->apiUrl, '/'), $this->botToken);

        try {
            $response = $this->http
                ->asJson()
                ->acceptJson()
                ->timeout($this->timeout)
                ->retry(3, 200, throw: false)
                ->post($endpoint, $message->toPayload());
        } catch (ConnectionException $e) {
            throw new TelegramApiException('Telegram API is unreachable.', previous: $e);
        }

        $body = $response->json();

        if (! $response->successful() || ! is_array($body) || ($body['ok'] ?? false) !== true) {
            throw TelegramApiException::fromResponse(
                $response->status(),
                is_array($body) ? ($body['description'] ?? null) : null,
                is_array($body) ? ($body['error_code'] ?? null) : null,
            );
        }

        $messageId = $body['result']['message_id'] ?? null;

        return is_int($messageId) ? $messageId : 0;
    }
}
