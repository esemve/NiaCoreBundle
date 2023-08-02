<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Manager;

use Nia\CoreBundle\Driver\QueueDriverInterface;
use Nia\CoreBundle\Entity\Queue;
use Nia\CoreBundle\ValueObject\QueueMessageInterface;

class QueueManager
{
    /**
     * @var QueueDriverInterface
     */
    private $driver;

    public function __construct(QueueDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function push(QueueMessageInterface $queueMessage): void
    {
        $this->driver->push($queueMessage);
    }

    public function pop(): ?Queue
    {
        return $this->driver->pop();
    }

    public function store(Queue $queue): void
    {
        $this->driver->store($queue);
    }

    public function remove(Queue $queue): void
    {
        $this->driver->remove($queue);
    }

    public function removeSuccessMessages(): void
    {
        $this->driver->removeSuccessMessages();
    }

    public function getCountNotStarted(): int
    {
        return $this->driver->countNotStarted() ?? 0;
    }

    public function getCountFail(): int
    {
        return $this->driver->countFail() ?? 0;
    }

    public function getCountAccumulated(): int
    {
        return $this->driver->countAccumulated() ?? 0;
    }

    public function getCountSuccess(): int
    {
        return $this->driver->countSuccess() ?? 0;
    }

    public function findById(string $id): ?Queue
    {
        return $this->driver->findById($id);
    }
}
