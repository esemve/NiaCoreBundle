<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nia\CoreBundle\Entity\Traits\DateTimedEntityTrait;
use Nia\CoreBundle\Entity\Traits\EntityTrait;
use Nia\CoreBundle\Entity\Traits\IdentifiableEntityTrait;
use Nia\CoreBundle\Entity\Traits\SoftDeleteEntityTrait;

/** @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class AbstractEntity implements Entity, IdentifiableEntityInterface, DateTimedEntityInterface, SoftDeleteEntityInterface
{
    use IdentifiableEntityTrait;
    use DateTimedEntityTrait;
    use SoftDeleteEntityTrait;
    use EntityTrait;
}
