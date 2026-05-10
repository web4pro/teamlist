<?php

declare(strict_types=1);

namespace App\Services\DateEvents\Triggers;

use App\Member;
use App\Services\DateEvents\Contracts\DateEventTrigger;
use App\Services\Telegram\Messages\TelegramMessage;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class BirthdayEventTrigger implements DateEventTrigger
{
    public function __construct(
        private readonly string $chatId,
        private readonly string $template,
    ) {
    }

    public function key(): string
    {
        return 'birthday';
    }

    public function membersFor(CarbonImmutable $date): Collection
    {
        $month = $date->month;
        $day = $date->day;
        $shouldCoverFeb29 = ! $date->isLeapYear() && $month === 3 && $day === 1;

        return Member::query()
            ->whereNotNull('birthday')
            ->whereHas('user', fn (Builder $q) => $q->where('active', 1))
            ->where(function (Builder $q) use ($month, $day, $shouldCoverFeb29): void {
                $q->where(function (Builder $inner) use ($month, $day): void {
                    $inner->whereRaw('MONTH(birthday) = ?', [$month])
                        ->whereRaw('DAY(birthday) = ?', [$day]);
                });

                if ($shouldCoverFeb29) {
                    $q->orWhere(function (Builder $inner): void {
                        $inner->whereRaw('MONTH(birthday) = ?', [2])
                            ->whereRaw('DAY(birthday) = ?', [29]);
                    });
                }
            })
            ->with(['user:id,active', 'department:id,name'])
            ->get();
    }

    public function buildMessage(Member $member, CarbonImmutable $date): TelegramMessage
    {
        $departmentName = $member->relationLoaded('department') && $member->department !== null
            ? (string) $member->department->name
            : '';

        $departmentSuffix = $departmentName !== ''
            ? ' (' . e($departmentName) . ')'
            : '';

        $text = strtr($this->template, [
            ':name' => e((string) $member->name),
            ':surname' => e((string) $member->surname),
            ':department' => e($departmentName),
            ':department_suffix' => $departmentSuffix,
        ]);

        return new TelegramMessage(
            chatId: $this->chatId,
            text: $text,
        );
    }
}
