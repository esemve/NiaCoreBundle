<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Test;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Esemve\Collection\AbstractTypedCollection;

abstract class DBALTestCase extends TestCase
{
    abstract protected function getTypeName(): string;

    abstract protected function getTypeClass(): string;

    /**
     * [ [sqlvalue, phpvalue] ]
     * example: [[',1,2,3,',IntegerCollection([1,2,3])]].
     *
     * @return array
     */
    abstract public function dataProviderConvertToDatabaseValue(): array;

    /**
     * [ [sqlvalue, phpvalue] ]
     * example: [[',1,2,3,',IntegerCollection([1,2,3])]].
     *
     * @return array
     */
    abstract public function dataProviderSqlDeclaration(): array;

    public function setUp()
    {
        try {
            Type::getType($this->getTypeName());
        } catch (\Exception $ex) {
            Type::addType($this->getTypeName(), $this->getTypeClass());
        }
    }

    protected function createType(): Type
    {
        return Type::getType($this->getTypeName());
    }

    public function testGetName()
    {
        $type = $this->createType();
        $this->assertSame($this->getTypeName(), $type->getName());
    }

    public function testGetSQLDeclaration(): void
    {
        $platform = $this->getMockBuilder(AbstractPlatform::class)->getMock();
        $platform->expects($this->once())
            ->method('getClobTypeDeclarationSQL');

        $type = $this->createType();
        $type->getSQLDeclaration([], $platform);
    }

    /**
     * @dataProvider dataProviderConvertToDatabaseValue
     */
    public function testConvertToDatabaseValue($sqlValue, $phpValue): void
    {
        $type = $this->createType();

        $this->assertSame(
            $sqlValue,
            $type->convertToDatabaseValue($phpValue, $this->createPlatform())
        );
    }

    /**
     * @dataProvider dataProviderSqlDeclaration
     */
    public function testSqlDeclaration($sqlValue, $phpValue): void
    {
        $type = $this->createType();

        if ($phpValue instanceof AbstractTypedCollection) {
            $phpValue = $phpValue->toArray();
        }

        $this->assertSame(
            $phpValue,
            $type->convertToPHPValue($sqlValue, $this->createPlatform())->toArray()
        );
    }

    protected function createPlatform(): AbstractPlatform
    {
        return $this->createMock(AbstractPlatform::class);
    }
}
