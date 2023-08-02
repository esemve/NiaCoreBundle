<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Collections;

use Esemve\Collection\AbstractTypedCollection;

class CriteriaCollection extends AbstractTypedCollection
{
    const ALLOWED = [
        '=',
        '<',
        '>',
        '<=',
        '>=',
        '!=',
        '<>',
        'IN',
        'NOT IN',
        'NOT LIKE',
        'LIKE',
        'LIKE%',
        '%LIKE',
        '%LIKE%',
        ',LIKE,',
    ];

    /**
     * Validate an element in array.
     *
     * @param $element
     *
     * @return bool
     */
    protected function isValid($element): bool
    {
        if (!\is_array($element)) {
            return false;
        }

        if (3 === \count($element)) {
            if (\in_array($element[1], self::ALLOWED, true)) {
                return true;
            }
        }

        return false;
    }
}
