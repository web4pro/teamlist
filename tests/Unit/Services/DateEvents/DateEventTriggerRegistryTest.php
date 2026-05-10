<?php

declare(strict_types=1);

namespace Tests\Unit\Services\DateEvents;

use App\Member;
use App\Services\DateEvents\Contracts\DateEventTrigger;
use App\Services\DateEvents\DateEventTriggerRegistry;
use App\Services\Telegram\Messages\TelegramMessage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DateEventTriggerRegistryTest extends TestCase
{
    public function testItIndexesTriggersByKey(): void
    {
        $a = $this->stubTrigger('birthday');
        $b = $this->stubTrigger('work_anniversary');

        $registry = new DateEventTriggerRegistry([$a, $b]);

        self::assertSame($a, $registry->get('birthday'));
        self::assertSame($b, $registry->get('work_anniversary'));
        self::assertCount(2, $registry->all());
    }

    public function testItThrowsForUnknownKey(): void
    {
        $registry = new DateEventTriggerRegistry([]);

        $this->expectException(InvalidArgumentException::class);
        $registry->get('nope');
    }

    private function stubTrigger(string $key): DateEventTrigger
    {
        return new class($key) implements DateEventTrigger {
            public function __construct(private readonly string $key)
            {
            }

            public function key(): string
            {
                return $this->key;
            }

            public function membersFor(CarbonImmutable $date): Collection
            {
                return new Collection();
            }

            public function buildMessage(Member $member, CarbonImmutable $date): TelegramMessage
            {
                return new TelegramMessage('0', '');
            }
        };
    }
}
