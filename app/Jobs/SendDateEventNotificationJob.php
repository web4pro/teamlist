<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Member;
use App\Services\DateEvents\DateEventNotificationService;
use App\Services\DateEvents\DateEventTriggerRegistry;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class SendDateEventNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var int */
    public $tries = 3;

    /** @var int */
    public $timeout = 30;

    public function __construct(
        public readonly string $triggerKey,
        public readonly int $memberId,
        public readonly string $date,
    ) {
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(
        DateEventTriggerRegistry $registry,
        DateEventNotificationService $service,
    ): void {
        $trigger = $registry->get($this->triggerKey);

        $member = Member::query()
            ->with(['user:id,active', 'department:id,name'])
            ->find($this->memberId);

        if ($member === null || $member->user === null || (int) $member->user->active !== 1) {
            return;
        }

        $service->notify($trigger, $member, CarbonImmutable::parse($this->date));
    }
}
