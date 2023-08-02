<?php

declare(strict_types=1);

namespace Nia\CoreBundle\ValueObject;

interface QueueMessageInterface
{
    public function setProcessable(\DateTimeInterface $dateTime): void;

    public function getProcessable(): \DateTimeInterface;

    public function setPriority(?int $priority = 5): void;

    public function getPriority(): int;

    public function setProcessed(): void;

    public function isProcessed(): bool;
}
