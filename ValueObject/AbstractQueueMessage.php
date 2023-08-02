<?php

declare(strict_types=1);

namespace Nia\CoreBundle\ValueObject;

abstract class AbstractQueueMessage implements QueueMessageInterface
{
    const PRIORITY_1 = 1; // Legkevésbé fontos
    const PRIORITY_2 = 2;
    const PRIORITY_3 = 3;
    const PRIORITY_4 = 4;
    const PRIORITY_5 = 5;
    const PRIORITY_6 = 6;
    const PRIORITY_7 = 7;
    const PRIORITY_8 = 8;
    const PRIORITY_9 = 9; // Leginkább fontos

    private $processable_start;

    private $isProcessed;

    private $priority;

    final public function setProcessable(\DateTimeInterface $dateTime): void
    {
        $this->processable_start = $dateTime;
    }

    final public function getProcessable(): \DateTimeInterface
    {
        if (null === $this->processable_start) {
            return new \DateTimeImmutable('now');
        }

        return $this->processable_start;
    }

    public function getSerialized()
    {
        return serialize($this);
    }

    public function setProcessed(): void
    {
        $this->isProcessed = true;
    }

    public function isProcessed(): bool
    {
        return (bool) $this->isProcessed;
    }

    public function setPriority(?int $priority = 5): void
    {
        if (!\in_array($priority, [self::PRIORITY_1, self::PRIORITY_2, self::PRIORITY_3, self::PRIORITY_4, self::PRIORITY_5, self::PRIORITY_6, self::PRIORITY_7, self::PRIORITY_8, self::PRIORITY_9], true)) {
            throw new \InvalidArgumentException($priority.' is not a valid Queue message priority!');
        }

        $this->priority = $priority;
    }

    public function getPriority(): int
    {
        return $this->priority ?? self::PRIORITY_5;
    }
}
