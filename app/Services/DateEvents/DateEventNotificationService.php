<?php

declare(strict_types=1);

namespace App\Services\DateEvents;

use App\Member;
use App\Services\DateEvents\Contracts\DateEventTrigger;
use App\Services\Telegram\Contracts\TelegramClientInterface;
use Carbon\CarbonImmutable;

final class DateEventNotificationService
{
    public function __construct(
        private readonly TelegramClientInterface $telegram,
    ) {
    }

    public function notify(DateEventTrigger $trigger, Member $member, CarbonImmutable $date): int
    {
        $message = $trigger->buildMessage($member, $date);

        return $this->telegram->send($message);
    }
}
