<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Manager;

use Nia\CoreBundle\Collections\LocaleCollection;
use Nia\CoreBundle\Tests\Collections\AbstractTypedCollectionTest;
use Nia\CoreBundle\ValueObject\Locale;

class LocaleCollectionTest extends AbstractTypedCollectionTest
{
    /**
     * Name of the tested Collection class.
     *
     * @return string
     */
    protected function getClassName(): string
    {
        return LocaleCollection::class;
    }

    public function getCollectedTypeClass(): string
    {
        return Locale::class;
    }
}
