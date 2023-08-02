<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Esemve\Collection\StringCollection;

class StringListType extends Type
{
    const TYPE_NAME = 'stringList';

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
        if ((empty($value)) || (0 === $value->count())) {
            return '';
        }

        return str_replace(self::SEPARATOR.self::SEPARATOR, self::SEPARATOR, self::SEPARATOR.implode(self::SEPARATOR, $value->all()).self::SEPARATOR);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value) || (',' === $value)) {
            return new StringCollection([]);
        }

        $value = explode(self::SEPARATOR, trim($value, self::SEPARATOR));
        $output = [];

        foreach ($value as $val) {
            if ((empty($val)) && ('' !== $val)) {
                continue;
            }
            $output[] = $val;
        }

        return new StringCollection((array) $output);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
