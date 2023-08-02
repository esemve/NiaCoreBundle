<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Security\QueryFilters;

use Doctrine\ORM\Query\Expr\Base;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Security\Context;

class CreateAndReadFilter extends RoleFilter
{
    public function canCreate(Entity $entity, Context $context): ?bool
    {
        return true;
    }

    public function getFilter(string $alias = null, Context $context): ?Base
    {
        return null;
    }
}
