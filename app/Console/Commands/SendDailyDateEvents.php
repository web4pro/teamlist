<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SendDateEventNotificationJob;
use App\Services\DateEvents\DateEventTriggerRegistry;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

final class SendDailyDateEvents extends Command
{
    protected $signature = 'events:send-daily
                            {--date= : Date in Y-m-d to evaluate against (defaults to today in events.timezone)}
                            {--dry-run : Resolve recipients and print them without dispatching jobs}';

    protected $description = 'Dispatch Telegram notifications for date-based employee events (birthday, etc.).';

    public function handle(DateEventTriggerRegistry $registry): int
    {
        $timezone = (string) config('events.timezone', 'UTC');
        $dateOption = $this->option('date');
        $date = $dateOption !== null && $dateOption !== ''
            ? CarbonImmutable::parse((string) $dateOption, $timezone)->startOfDay()
            : CarbonImmutable::now($timezone)->startOfDay();

        $isDryRun = (bool) $this->option('dry-run');

        $totalDispatched = 0;
        $totalCandidates = 0;

        foreach ($registry->all() as $trigger) {
            $members = $trigger->membersFor($date);
            $totalCandidates += $members->count();

            $this->line(sprintf(
                '[%s] %s: %d candidate(s) on %s',
                $trigger->key(),
                $isDryRun ? 'DRY-RUN' : 'dispatch',
                $members->count(),
                $date->toDateString(),
            ));

            foreach ($members as $member) {
                $this->line(sprintf(
                    '  - #%d %s %s',
                    (int) $member->user_id,
                    (string) $member->name,
                    (string) $member->surname,
                ));

                if ($isDryRun) {
                    continue;
                }

                SendDateEventNotificationJob::dispatch(
                    $trigger->key(),
                    (int) $member->user_id,
                    $date->toDateString(),
                );
                $totalDispatched++;
            }
        }

        $this->info(sprintf(
            'Done. Candidates: %d, dispatched: %d%s.',
            $totalCandidates,
            $totalDispatched,
            $isDryRun ? ' (dry-run)' : '',
        ));

        return self::SUCCESS;
    }
}
