<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Constraint;

use Symfony\Component\Validator\Constraint;

class UniqueField extends Constraint
{
    public $message = 'NiaCoreBundle@error.value_used';
    public $entity = null;
    public $field = '';
    public $context;

    public function getRequiredOptions(): array
    {
        return ['entity', 'field', 'context'];
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
