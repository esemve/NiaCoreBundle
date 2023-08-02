<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Manager;

use Nia\CoreBundle\Collections\EntityReferenceCollection;
use Nia\CoreBundle\Entity\EntityReference;
use Nia\CoreBundle\Tests\Collections\AbstractTypedCollectionTest;

class EntityReferenceCollectionTest extends AbstractTypedCollectionTest
{
    /**
     * Name of the tested Collection class.
     *
     * @return string
     */
    protected function getClassName(): string
    {
        return EntityReferenceCollection::class;
    }

    public function getCollectedTypeClass(): string
    {
        return EntityReference::class;
    }
}
