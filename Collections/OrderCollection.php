<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Collections;

use Esemve\Collection\AbstractTypedCollection;
use Nia\CoreBundle\Enum\OrderEnum;

class OrderCollection extends AbstractTypedCollection
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
        if ($element instanceof OrderEnum) {
            return true;
        }

        return false;
    }
}
