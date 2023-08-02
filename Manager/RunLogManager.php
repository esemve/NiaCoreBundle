<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Nia\CoreBundle\Entity\RunLog;
use Nia\CoreBundle\Repository\RunLogRepository;

class RunLogManager
{
    /**
     * @var RunLogRepository
     */
    private $repository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(RunLog::class);
        $this->entityManager = $entityManager;
    }

    public function getTime(string $key): ?\DateTimeInterface
    {
        /** @var RunLog $entity */
        $entity = $this->repository->find($key);

        if (empty($entity)) {
            return null;
        }

        return $entity->getTime();
    }

    public function update(string $key): void
    {
        $entity = $this->repository->find($key);

        if (empty($entity)) {
            $entity = new RunLog();
            $entity->setKey($key);
        }
        $entity->setTime(new \DateTime('now'));

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }
}
