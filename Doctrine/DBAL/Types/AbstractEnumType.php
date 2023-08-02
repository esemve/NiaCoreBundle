<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Nia\CoreBundle\Enum\AbstractEnum;

abstract class AbstractEnumType extends Type
{
    protected $name;
    protected $values = [];

    abstract public function getEnumClass(): string;

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'INT';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $class = $this->getEnumClass();

        return new $class((int) $value);
    }

    /**
     * @param Enum             $value
     * @param AbstractPlatform $platform
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ((!($value instanceof AbstractEnum)) && (!\is_int($value))) {
            throw new \InvalidArgumentException('Not valid enum for the database!');
        }

        if (\is_int($value)) {
            return $value;
        }

        return $value->getValue();
    }

    public function getName()
    {
        return $this->name;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
