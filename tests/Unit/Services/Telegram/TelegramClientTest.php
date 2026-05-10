<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Telegram;

use App\Services\Telegram\Exceptions\TelegramApiException;
use App\Services\Telegram\Messages\TelegramMessage;
use App\Services\Telegram\TelegramClient;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request;
use PHPUnit\Framework\TestCase;

class TelegramClientTest extends TestCase
{
    public function testItPostsToSendMessageEndpointWithExpectedPayload(): void
    {
        $http = new HttpFactory();
        $http->fake([
            '*' => $http->response([
                'ok' => true,
                'result' => ['message_id' => 4242],
            ], 200),
        ]);

        $client = new TelegramClient($http, 'https://api.telegram.org/', 'TEST_TOKEN', 5);

        $messageId = $client->send(new TelegramMessage(
            chatId: '-100123',
            text: '<b>hi</b>',
        ));

        self::assertSame(4242, $messageId);

        $http->assertSent(function (Request $request) {
            return $request->url() === 'https://api.telegram.org/botTEST_TOKEN/sendMessage'
                && $request->method() === 'POST'
                && $request['chat_id'] === '-100123'
                && $request['text'] === '<b>hi</b>'
                && $request['parse_mode'] === 'HTML'
                && $request['disable_web_page_preview'] === true;
        });
    }

    public function testItThrowsOnHttpErrorResponse(): void
    {
        $http = new HttpFactory();
        $http->fake([
            '*' => $http->response([
                'ok' => false,
                'error_code' => 400,
                'description' => 'Bad Request: chat not found',
            ], 400),
        ]);

        $client = new TelegramClient($http, 'https://api.telegram.org', 'T', 5);

        $this->expectException(TelegramApiException::class);
        $this->expectExceptionMessageMatches('/chat not found/');

        $client->send(new TelegramMessage('-1', 'hi'));
    }

    public function testItThrowsWhenBotTokenIsMissing(): void
    {
        $http = new HttpFactory();
        $client = new TelegramClient($http, 'https://api.telegram.org', '', 5);

        $this->expectException(TelegramApiException::class);
        $this->expectExceptionMessageMatches('/bot token/i');

        $client->send(new TelegramMessage('-1', 'hi'));
    }
}
