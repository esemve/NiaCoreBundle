<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Security\QueryFilters;

use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Security\Context;

class CreateAndDeleteGrantIfHasEditRole extends RoleFilter
{
    public function canCreate(Entity $entity, Context $context): ?bool
    {
        return $this->canEdit($entity, $context) || parent::canCreate($entity, $context);
    }

    public function canDelete(Entity $entity, Context $context): ?bool
    {
        return $this->canEdit($entity, $context) || parent::canDelete($entity, $context);
    }
}
