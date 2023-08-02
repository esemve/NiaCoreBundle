<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Manager;

use Nia\CoreBundle\Collections\EntityCollection;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Tests\Collections\AbstractTypedCollectionTest;

class EntityCollectionTest extends AbstractTypedCollectionTest
{
    /**
     * Name of the tested Collection class.
     *
     * @return string
     */
    protected function getClassName(): string
    {
        return EntityCollection::class;
    }

    public function getCollectedTypeClass(): string
    {
        return Entity::class;
    }
}
