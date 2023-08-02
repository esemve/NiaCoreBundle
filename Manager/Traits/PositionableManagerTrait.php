<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Manager\Traits;

use Nia\CoreBundle\Collections\CriteriaCollection;
use Nia\CoreBundle\Entity\PositionableEntity;
use Nia\CoreBundle\Enum\OrderEnum;
use Nia\CoreBundle\Exception\InvalidConfigrationException;
use Nia\CoreBundle\Exception\NotFoundException;
use Nia\CoreBundle\Security\Context;

trait PositionableManagerTrait
{
    public function getNextPosition(CriteriaCollection $criteria, Context $context): int
    {
        if (!($this->createNew() instanceof PositionableEntity)) {
            throw new InvalidConfigrationException(sprintf('%s is not a positionable entity!', $this->getEntityClass()));
        }

        try {
            $nextPosition = $this->findOneby($criteria, $context, 'position', $this->getEnumFactory()->create(OrderEnum::class, OrderEnum::DESC));
            $nextPosition = $nextPosition->getPosition() + 1;
        } catch (NotFoundException $ex) {
            $nextPosition = 1;
        }

        return $nextPosition;
    }

    protected function doIncreasePositions(CriteriaCollection $criteriaCollection, int $number, Context $context): void
    {
        $class = \get_class($this->getSampleEntity());

        $dql = 'update '.$class.' p set p.position = p.position+1 WHERE p.position > '.$number;

        foreach ($criteriaCollection as $criteria) {
            $dql .= ' AND p.'.$criteria[0].' '.$criteria[1].' \''.$criteria[2].'\'';
        }

        $query = $this->getEntityManager()->createQuery($dql);
        $query->execute();
    }
}
