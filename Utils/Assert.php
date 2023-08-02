<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Utils;

use Esemve\Collection\Exception\InvalidTypeException;

class Assert
{
    public static function checkInstance($value, string $className): void
    {
        if (!($value instanceof $className)) {
            throw new InvalidTypeException();
        }
    }

    public static function checkListInstance(array $values = null, string $className): void
    {
        if (null !== $values) {
            foreach ($values as $value) {
                self::checkInstance($value, $className);
            }
        }
    }

    public static function checkIsStringList(array $values = null): void
    {
        if (null !== $values) {
            foreach ($values as $value) {
                if ((!\is_string($value)) && (null !== $value)) {
                    throw new InvalidTypeException();
                }
            }
        }
    }

    public static function checkIsIntList(array $values = null): void
    {
        if (null !== $values) {
            foreach ($values as $value) {
                if ((!\is_int($value)) && (null !== $value)) {
                    throw new InvalidTypeException();
                }
            }
        }
    }
}
