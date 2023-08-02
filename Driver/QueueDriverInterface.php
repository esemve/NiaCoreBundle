<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Driver;

use Nia\CoreBundle\Entity\Queue;
use Nia\CoreBundle\ValueObject\QueueMessageInterface;

interface QueueDriverInterface
{
    public function push(QueueMessageInterface $queueMessage): void;

    public function pop(): ?Queue;

    public function findById(string $id): ?Queue;

    public function store(Queue $queue): void;

    public function remove(Queue $queue): void;

    public function removeSuccessMessages(): void;

    public function countNotStarted(): int;

    public function countAccumulated(): int;

    public function countFail(): int;

    public function countSuccess(): int;
}
