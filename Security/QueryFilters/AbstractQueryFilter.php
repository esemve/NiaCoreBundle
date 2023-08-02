<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Security\QueryFilters;

use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Security\Context;

abstract class AbstractQueryFilter implements QueryFilterInterface
{
    protected $tokenStorage;

    protected $tokenRoles;

    protected function createAlias(string $alias = null): ?string
    {
        if (null !== $alias) {
            $alias = $alias.'.';
        }

        return $alias;
    }

    public function canEdit(Entity $entity, Context $context): ?bool
    {
        return null;
    }

    public function canDelete(Entity $entity, Context $context): ?bool
    {
        return null;
    }

    public function canCreate(Entity $entity, Context $context): ?bool
    {
        return null;
    }

    protected function hasRole(string $role, Context $context): bool
    {
        return $context->hasRole($role);
    }
}
