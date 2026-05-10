<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Jobs\SendDateEventNotificationJob;
use App\Member;
use App\Services\DateEvents\Contracts\DateEventTrigger;
use App\Services\DateEvents\DateEventTriggerRegistry;
use App\Services\Telegram\Messages\TelegramMessage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SendDailyDateEventsCommandTest extends TestCase
{
    public function testItDispatchesOneJobPerMemberPerTrigger(): void
    {
        Bus::fake();

        $alice = $this->fakeMember(1, 'Alice', 'A');
        $bob = $this->fakeMember(2, 'Bob', 'B');
        $cara = $this->fakeMember(3, 'Cara', 'C');

        $this->bindRegistry([
            new InMemoryTrigger('birthday', new Collection([$alice, $bob])),
            new InMemoryTrigger('work_anniversary', new Collection([$cara])),
        ]);

        $this->artisan('events:send-daily', ['--date' => '2026-05-08'])
            ->assertExitCode(0);

        Bus::assertDispatchedTimes(SendDateEventNotificationJob::class, 3);
        Bus::assertDispatched(SendDateEventNotificationJob::class, fn ($job) =>
            $job->triggerKey === 'birthday' && $job->memberId === 1 && $job->date === '2026-05-08');
        Bus::assertDispatched(SendDateEventNotificationJob::class, fn ($job) =>
            $job->triggerKey === 'work_anniversary' && $job->memberId === 3 && $job->date === '2026-05-08');
    }

    public function testDryRunDoesNotDispatch(): void
    {
        Bus::fake();

        $alice = $this->fakeMember(1, 'Alice', 'A');
        $this->bindRegistry([new InMemoryTrigger('birthday', new Collection([$alice]))]);

        $this->artisan('events:send-daily', ['--date' => '2026-05-08', '--dry-run' => true])
            ->assertExitCode(0);

        Bus::assertNothingDispatched();
    }

    /**
     * @param array<int, DateEventTrigger> $triggers
     */
    private function bindRegistry(array $triggers): void
    {
        $this->app->instance(
            DateEventTriggerRegistry::class,
            new DateEventTriggerRegistry($triggers),
        );
    }

    private function fakeMember(int $id, string $name, string $surname): Member
    {
        $member = new Member(['name' => $name, 'surname' => $surname]);
        $member->user_id = $id;

        return $member;
    }
}

final class InMemoryTrigger implements DateEventTrigger
{
    public function __construct(
        private readonly string $key,
        private readonly Collection $members,
    ) {
    }

    public function key(): string
    {
        return $this->key;
    }

    public function membersFor(CarbonImmutable $date): Collection
    {
        return $this->members;
    }

    public function buildMessage(Member $member, CarbonImmutable $date): TelegramMessage
    {
        return new TelegramMessage('0', '');
    }
}
