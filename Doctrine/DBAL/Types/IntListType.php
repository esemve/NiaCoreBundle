<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Esemve\Collection\IntegerCollection;

class IntListType extends Type
{
    const TYPE_NAME = 'intList';

    const SEPARATOR = ',';

    public function getName()
    {
        return self::TYPE_NAME;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return '';
        }

        if ($value instanceof IntegerCollection) {
            $data = $value->all();
        } elseif (\is_array($value)) {
            $data = $value;
        } else {
            return '';
        }

        return str_replace(self::SEPARATOR.self::SEPARATOR, self::SEPARATOR, self::SEPARATOR.implode(self::SEPARATOR, $data).self::SEPARATOR);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return new IntegerCollection([]);
        }

        $value = explode(self::SEPARATOR, trim($value, self::SEPARATOR));

        $output = [];

        foreach ($value as $val) {
            if ((empty($val)) && (0 !== $val)) {
                continue;
            }
            $output[] = (int) $val;
        }

        return new IntegerCollection((array) $output);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
