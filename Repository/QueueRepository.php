<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Nia\CoreBundle\Entity\Queue;

class QueueRepository extends EntityRepository
{
    public function pop(): ?Queue
    {
        $lockTime = new \DateTimeImmutable('now');

        $qb = $this->createQueryBuilder('u');
        $qb->where('u.processable_start <= :now')
            ->andWhere('u.success IS NULL')
            ->andWhere('u.locked < :locked OR u.locked IS NULL');
        $qb->setParameter('now', date('Y-m-d H:i:s'));
        $qb->setParameter('locked', date('Y-m-d H:i:s'));
        $qb->orderBy('u.priority', 'DESC');
        $qb->addOrderBy('u.processable_start', 'ASC');
        $qb->setMaxResults(1);

        /** @var Queue $entity */
        $entity = $qb->getQuery()->getResult();

        if (empty($entity)) {
            return null;
        }

        $entity = $entity[0];

        if ($entity->getLocked() instanceof \DateTimeInterface) {
            $qb = $this->createQueryBuilder('u')
                ->update(Queue::class, 'u')
                ->set('u.locked', '?1')
                ->where('u.id = ?2')
                ->andWhere('u.locked = ?3')
                ->setParameter(1, date('Y-m-d H:i:s'))
                ->setParameter(2, $entity->getId())
                ->setParameter(3, $entity->getLocked()->format('Y-m-d H:i:s'))
                ->getQuery();
        } elseif (null === $entity->getLocked()) {
            $qb = $this->createQueryBuilder('u')
                ->update(Queue::class, 'u')
                ->set('u.locked', '?1')
                ->where('u.id = ?2')
                ->andWhere('u.locked IS NULL')
                ->setParameter(1, date('Y-m-d H:i:s'))
                ->setParameter(2, $entity->getId())
                ->getQuery();
        }

        $p = $qb->execute();

        if (1 === $p) {
            $entity->setLocked($lockTime);
            $this->getEntityManager()->getUnitOfWork()->refresh($entity);

            return $entity;
        }

        return null;
    }

    public function removeSuccessMessages(): void
    {
        $this->createQueryBuilder('u')
            ->delete()
            ->where('u.success IS NOT NULL')
            ->getQuery()->execute();
    }

    public function countNotStarted(): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.success IS NULL')
            ->andWhere('u.fail_count IS NULL');

        $count = $qb->getQuery()->getSingleScalarResult();

        return (int) $count;
    }

    public function countAccumulated(): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.success IS NULL')
            ->andWhere('u.processable_start < :now')
            ->andWhere('u.fail_count IS NULL')
            ->setParameter(':now', date('Y-m-d H:i:s'));

        $count = $qb->getQuery()->getSingleScalarResult();

        return (int) $count;
    }

    public function countFail(): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.success IS NULL')
            ->andWhere('u.fail_count > 0');

        $count = $qb->getQuery()->getSingleScalarResult();

        return (int) $count;
    }

    public function countSuccess(): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.success IS NOT NULL');

        $count = $qb->getQuery()->getSingleScalarResult();

        return (int) $count;
    }

    public function remove(Queue $queue): void
    {
        $this->getEntityManager()->remove($queue);
        $this->getEntityManager()->flush();
    }
}
