<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Doctrine\DBAL\Types;

use Esemve\Collection\IntegerCollection;
use Esemve\Collection\StringCollection;
use Nia\CoreBundle\Doctrine\DBAL\Types\StringListType;
use Nia\CoreBundle\Test\DBALTestCase;

class StringListTypeTest extends DBALTestCase
{
    protected function getTypeName(): string
    {
        return 'stringList';
    }

    protected function getTypeClass(): string
    {
        return StringListType::class;
    }

    /**
     * [ [sqlvalue, phpvalue] ]
     * example: [[',1,2,3,',IntegerCollection([1,2,3])]].
     *
     * @return array
     */
    public function dataProviderConvertToDatabaseValue(): array
    {
        return [
            [
                ',aa,b b,cc,',
                $this->createStringCollection(['aa', 'b b', 'cc']),
            ],
            [
                '',
                $this->createStringCollection([]),
            ],
            [
                ',a,b,c,d,e,',
                $this->createStringCollection(['a', 'b', 'c', 'd', 'e']),
            ],
        ];
    }

    /**
     * [ [sqlvalue, phpvalue] ]
     * example: [[',1,2,3,',IntegerCollection([1,2,3])]].
     *
     * @return array
     */
    public function dataProviderSqlDeclaration(): array
    {
        return $this->dataProviderConvertToDatabaseValue();
    }

    protected function createStringCollection(array $strings): StringCollection
    {
        $collectionFactory = $this->getContainer()->get('nia.core.collection.factory');

        return $collectionFactory->createStringCollection($strings);
    }
}
