<?php

declare(strict_types=1);

namespace App\Services\Telegram\Contracts;

use App\Services\Telegram\Messages\TelegramMessage;

interface TelegramClientInterface
{
    /**
     * Send a message to Telegram and return the message_id from the API.
     *
     * @throws \App\Services\Telegram\Exceptions\TelegramApiException
     */
    public function send(TelegramMessage $message): int;
}
