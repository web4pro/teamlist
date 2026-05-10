<?php

declare(strict_types=1);

namespace App\Services\Telegram\Messages;

final class TelegramMessage
{
    public function __construct(
        public readonly string $chatId,
        public readonly string $text,
        public readonly string $parseMode = 'HTML',
        public readonly bool $disableWebPagePreview = true,
    ) {
    }

    public function toPayload(): array
    {
        return [
            'chat_id' => $this->chatId,
            'text' => $this->text,
            'parse_mode' => $this->parseMode,
            'disable_web_page_preview' => $this->disableWebPagePreview,
        ];
    }
}