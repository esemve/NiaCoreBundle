<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Manager;

use Nia\CoreBundle\Collections\CriteriaCollection;
use Nia\CoreBundle\Entity\PositionableEntity;
use Nia\CoreBundle\Enum\OrderEnum;
use Nia\CoreBundle\Security\Context;

interface PositionableManagerInterface
{
    public function positionOrdering(): OrderEnum;

    public function getPositionCriteria(PositionableEntity $entity, Context $context): CriteriaCollection;

    public function getNextPosition(CriteriaCollection $criteria, Context $context): int;
}
