<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Security\QueryFilters;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Base;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Entity\StatusableInterace;
use Nia\CoreBundle\Security\Context;

class ActiveStatusQueryFilter extends AbstractQueryFilter
{
    public function isSupported(Entity $entity): bool
    {
        return $entity instanceof StatusableInterace;
    }

    public function getFilter(?string $alias, Context $context): ?Base
    {
        return new Andx(sprintf('%sstatus = 1', $this->createAlias($alias)));
    }
}
