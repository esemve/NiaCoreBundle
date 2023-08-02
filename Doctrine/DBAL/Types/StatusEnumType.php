<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Doctrine\DBAL\Types;

use Nia\CoreBundle\Enum\StatusEnum;

class StatusEnumType extends AbstractEnumType
{
    const TYPE_NAME = 'statusEnum';

    public function getName()
    {
        return self::TYPE_NAME;
    }

    public function getEnumClass(): string
    {
        return StatusEnum::class;
    }
}
