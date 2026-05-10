<?php

declare(strict_types=1);

namespace Tests\Unit\Services\DateEvents;

use App\Department;
use App\Member;
use App\Services\DateEvents\Triggers\BirthdayEventTrigger;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class BirthdayEventTriggerMessageTest extends TestCase
{
    public function testItRendersTemplateWithEscapedFields(): void
    {
        $trigger = new BirthdayEventTrigger(
            chatId: '-100',
            template: '<b>:name :surname</b>:department_suffix',
        );

        $department = new Department(['name' => 'R&D']);
        $member = new Member(['name' => 'Ada', 'surname' => "<O'Reilly>"]);
        $member->setRelation('department', $department);

        $message = $trigger->buildMessage($member, CarbonImmutable::parse('2026-05-08'));

        self::assertSame('-100', $message->chatId);
        self::assertSame(
            '<b>Ada &lt;O&#039;Reilly&gt;</b> (R&amp;D)',
            $message->text,
        );
    }

    public function testItOmitsDepartmentSuffixWhenNoDepartment(): void
    {
        $trigger = new BirthdayEventTrigger('-100', ':name:department_suffix');

        $member = new Member(['name' => 'Bob', 'surname' => 'B']);

        $message = $trigger->buildMessage($member, CarbonImmutable::parse('2026-05-08'));

        self::assertSame('Bob', $message->text);
    }

    public function testKeyIsBirthday(): void
    {
        $trigger = new BirthdayEventTrigger('-100', '');

        self::assertSame('birthday', $trigger->key());
    }
}
