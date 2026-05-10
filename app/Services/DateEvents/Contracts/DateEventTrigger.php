<?php

declare(strict_types=1);

namespace App\Services\DateEvents\Contracts;

use App\Member;
use App\Services\Telegram\Messages\TelegramMessage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

interface DateEventTrigger
{
    /**
     * Stable machine key, e.g. 'birthday' or 'work_anniversary'.
     * Used for routing messages from a queued job back to its trigger
     * and as a key into the templates config.
     */
    public function key(): string;

    /**
     * Members that should be notified on the given date in the configured timezone.
     *
     * @return Collection<int, Member>
     */
    public function membersFor(CarbonImmutable $date): Collection;

    /**
     * Build the outbound Telegram message for a given member on a given date.
     */
    public function buildMessage(Member $member, CarbonImmutable $date): TelegramMessage;
}
