<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Manager;

use Nia\CoreBundle\Collections\OrderCollection;
use Nia\CoreBundle\Enum\OrderEnum;
use Nia\CoreBundle\Tests\Collections\AbstractTypedCollectionTest;

class OrderCollectionTest extends AbstractTypedCollectionTest
{
    /**
     * Name of the tested Collection class.
     *
     * @return string
     */
    protected function getClassName(): string
    {
        return OrderCollection::class;
    }

    public function getCollectedTypeClass(): string
    {
        return OrderEnum::class;
    }
}
