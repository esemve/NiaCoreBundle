<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Collections;

use Esemve\Collection\AbstractTypedCollection;
use Nia\CoreBundle\Entity\Entity;

class EntityCollection extends AbstractTypedCollection
{
    /**
     * Validate an element in array.
     *
     * @param $element
     *
     * @return bool
     */
    protected function isValid($element): bool
    {
        if ($element instanceof Entity) {
            return true;
        }

        return false;
    }
}
