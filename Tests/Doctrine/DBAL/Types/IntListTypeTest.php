<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Doctrine\DBAL\Types;

use Esemve\Collection\IntegerCollection;
use Nia\CoreBundle\Doctrine\DBAL\Types\IntListType;
use Nia\CoreBundle\Test\DBALTestCase;

class IntListTypeTest extends DBALTestCase
{
    protected function getTypeName(): string
    {
        return 'intList';
    }

    protected function getTypeClass(): string
    {
        return IntListType::class;
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
                ',1,2,3,',
                $this->createIntegerCollection([1, 2, 3]),
            ],
            [
                ',',
                $this->createIntegerCollection([]),
            ],
            [
                ',1,2,3,4,5,',
                $this->createIntegerCollection([1, 2, 3, 4, 5]),
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
        return [
            [
                ',1,2,3,',
                $this->createIntegerCollection([1, 2, 3]),
            ],
            [
                '',
                $this->createIntegerCollection([]),
            ],
            [
                ',',
                $this->createIntegerCollection([]),
            ],
            [
                ',1,2,3,4,5,',
                $this->createIntegerCollection([1, 2, 3, 4, 5]),
            ],
            [
                ',1,,3,,5,',
                $this->createIntegerCollection([1, 3, 5]),
            ],
        ];
    }

    protected function createIntegerCollection(array $numbers): IntegerCollection
    {
        $collectionFactory = $this->getContainer()->get('nia.core.collection.factory');

        return $collectionFactory->createIntegerCollection($numbers);
    }
}
