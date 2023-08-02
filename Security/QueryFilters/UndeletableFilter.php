<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Security\QueryFilters;

use Doctrine\ORM\Query\Expr\Base;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Entity\UndeletableEntityInterface;
use Nia\CoreBundle\Security\Context;

class UndeletableFilter extends AbstractQueryFilter
{
    public function isSupported(Entity $entity): bool
    {
        return $entity instanceof UndeletableEntityInterface;
    }

    public function getFilter(string $alias = null, Context $context): ?Base
    {
        return null;
    }

    public function canDelete(Entity $entity, Context $context): ?bool
    {
        if ($entity->isUndeletable()) {
            return false;
        }

        return true;
    }
}
