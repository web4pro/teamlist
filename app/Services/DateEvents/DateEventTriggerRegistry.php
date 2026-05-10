<?php

declare(strict_types=1);

namespace App\Services\DateEvents;

use App\Services\DateEvents\Contracts\DateEventTrigger;
use InvalidArgumentException;

final class DateEventTriggerRegistry
{
    /** @var array<string, DateEventTrigger> */
    private array $triggers = [];

    /**
     * @param iterable<DateEventTrigger> $triggers
     */
    public function __construct(iterable $triggers)
    {
        foreach ($triggers as $trigger) {
            $this->triggers[$trigger->key()] = $trigger;
        }
    }

    /**
     * @return array<string, DateEventTrigger>
     */
    public function all(): array
    {
        return $this->triggers;
    }

    public function get(string $key): DateEventTrigger
    {
        if (! isset($this->triggers[$key])) {
            throw new InvalidArgumentException("Unknown date event trigger: {$key}");
        }

        return $this->triggers[$key];
    }
}
