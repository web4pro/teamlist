<?php

declare(strict_types=1);

namespace App\Services\Telegram\Exceptions;

use RuntimeException;
use Throwable;

class TelegramApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $errorCode = null,
        public readonly ?string $description = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function fromResponse(int $status, ?string $description, ?int $errorCode): self
    {
        $message = sprintf(
            'Telegram API error (HTTP %d, code %s): %s',
            $status,
            $errorCode === null ? '-' : (string) $errorCode,
            $description ?? 'Unknown error',
        );

        return new self($message, $errorCode, $description);
    }
}
