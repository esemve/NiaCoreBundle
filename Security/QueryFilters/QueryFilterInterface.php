<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Security\QueryFilters;

use Doctrine\ORM\Query\Expr\Base;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Security\Context;

interface QueryFilterInterface
{
    public function isSupported(Entity $entity): bool;

    public function getFilter(?string $alias, Context $context): ?Base;

    public function canEdit(Entity $entity, Context $context): ?bool;

    public function canDelete(Entity $entity, Context $context): ?bool;

    public function canCreate(Entity $entity, Context $context): ?bool;
}
