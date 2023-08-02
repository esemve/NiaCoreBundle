<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Security\QueryFilters;

use Doctrine\ORM\Query\Expr\Base;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Security\Context;

class UnitTestQueryFilter extends AbstractQueryFilter
{
    public function getFilter(string $alias = null, Context $context): ?Base
    {
        $this->stopIfNotTestEnv($context);

        return null;
    }

    public function canEdit(Entity $entity, Context $context): ?bool
    {
        $this->stopIfNotTestEnv($context);

        return true;
    }

    public function canCreate(Entity $entity, Context $context): ?bool
    {
        $this->stopIfNotTestEnv($context);

        return true;
    }

    public function canDelete(Entity $entity, Context $context): ?bool
    {
        $this->stopIfNotTestEnv($context);

        return true;
    }

    public function isSupported(Entity $entity): bool
    {
        return true;
    }

    private function stopIfNotTestEnv(Context $context): void
    {
        if ('test' !== $context->getEnv()) {
            throw new \Exception('You can use the UnitTestQueryFilter only in the test env!');
        }
    }
}
