<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Security\QueryFilters;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Base;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Exception\AccessDeniedException;
use Nia\CoreBundle\Security\Context;

class RoleFilter extends AbstractQueryFilter
{
    protected $roles = [];

    protected $roleGroup;

    public function isSupported(Entity $entity): bool
    {
        $this->roleGroup = $entity->getManager()->getRoleGroup();

        return $entity instanceof Entity;
    }

    public function getFilter(string $alias = null, Context $context): ?Base
    {
        if ($this->hasRole('ROLE_'.$this->roleGroup.'_SHOW', $context)) {
            return null;
        }

        if ($context->isDev()) {
            throw new AccessDeniedException(sprintf('%s throw access denied with token %s with roles %s',
                __CLASS__,
                \get_class($context->getToken()),
                implode(',', $context->getRoles())));
        }

        return new Andx(sprintf('1 = 2', $this->createAlias($alias)));
    }

    public function canEdit(Entity $entity, Context $context): ?bool
    {
        if ($this->hasRole('ROLE_'.$this->roleGroup.'_EDIT', $context)) {
            return true;
        }

        return false;
    }

    public function canCreate(Entity $entity, Context $context): ?bool
    {
        if ($this->hasRole('ROLE_'.$this->roleGroup.'_CREATE', $context)) {
            return true;
        }

        return false;
    }

    public function canDelete(Entity $entity, Context $context): ?bool
    {
        if ($this->hasRole('ROLE_'.$this->roleGroup.'_DELETE', $context)) {
            return true;
        }

        return false;
    }
}
