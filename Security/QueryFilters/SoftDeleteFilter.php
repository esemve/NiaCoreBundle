<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Security\QueryFilters;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Base;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Entity\SoftDeleteEntityInterface;
use Nia\CoreBundle\Security\Context;

class SoftDeleteFilter extends AbstractQueryFilter
{
    public function isSupported(Entity $entity): bool
    {
        return $entity instanceof SoftDeleteEntityInterface;
    }

    public function getFilter(?string $alias, Context $context): ?Base
    {
        return new Andx(sprintf('%sdeleted_at IS NULL', $this->createAlias($alias)));
    }
}
