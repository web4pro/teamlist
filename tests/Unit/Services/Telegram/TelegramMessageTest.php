<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Telegram;

use App\Services\Telegram\Messages\TelegramMessage;
use PHPUnit\Framework\TestCase;

class TelegramMessageTest extends TestCase
{
    public function testItBuildsExpectedPayload(): void
    {
        $message = new TelegramMessage('-100', 'hello');

        self::assertSame([
            'chat_id' => '-100',
            'text' => 'hello',
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ], $message->toPayload());
    }
}
