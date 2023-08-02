<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Security\QueryFilters;

use Doctrine\ORM\Query\Expr\Base;
use Nia\CoreBundle\Security\Context;

class ReadOnlyFilter extends RoleFilter
{
    public function getFilter(string $alias = null, Context $context): ?Base
    {
        return null;
    }
}
