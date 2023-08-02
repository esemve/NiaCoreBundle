<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Doctrine\DBAL\Types;

use Nia\CoreBundle\Enum\LogEventEnum;

class LogEventEnumType extends AbstractEnumType
{
    const TYPE_NAME = 'logEventEnum';

    public function getName()
    {
        return self::TYPE_NAME;
    }

    public function getEnumClass(): string
    {
        return LogEventEnum::class;
    }
}
