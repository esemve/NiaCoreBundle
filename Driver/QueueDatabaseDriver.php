<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Driver;

use Nia\CoreBundle\Entity\Manager\EntityManager;
use Nia\CoreBundle\Entity\Queue;
use Nia\CoreBundle\Repository\QueueRepository;
use Nia\CoreBundle\ValueObject\QueueMessageInterface;

class QueueDatabaseDriver implements QueueDriverInterface
{
    protected $entityManager;

    /**
     * @var QueueRepository
     */
    protected $repository;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Queue::class);
    }

    public function push(QueueMessageInterface $queueMessage): void
    {
        $queue = $this->createNew();
        $queue->setId((string) microtime(true));
        $queue->setProcessableStart($queueMessage->getProcessable());
        $queue->setPriority($queueMessage->getPriority());
        $queue->setMessage($queueMessage);
        $this->entityManager->persist($queue);
        $this->entityManager->flush();
    }

    public function pop(): ?Queue
    {
        return $this->repository->pop();
    }

    public function store(Queue $queue): void
    {
        $this->entityManager->persist($queue);
        $this->entityManager->flush();
    }

    public function removeSuccessMessages(): void
    {
        $this->repository->removeSuccessMessages();
    }

    public function countNotStarted(): int
    {
        return $this->repository->countNotStarted();
    }

    public function countAccumulated(): int
    {
        return $this->repository->countAccumulated();
    }

    public function countFail(): int
    {
        return $this->repository->countFail();
    }

    public function countSuccess(): int
    {
        return $this->repository->countSuccess();
    }

    protected function createNew(): Queue
    {
        return new Queue();
    }

    public function remove(Queue $queue): void
    {
        $this->repository->remove($queue);
    }

    public function findById(string $id): ?Queue
    {
        return $this->repository->find($id);
    }
}
