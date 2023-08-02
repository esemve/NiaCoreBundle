<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Utils;

use Nia\CoreBundle\Test\TestCase;
use Nia\CoreBundle\Utils\Assert;

class AssertTest extends TestCase
{
    /**
     * @dataProvider positiveCheckIsStringListDataProvider
     */
    public function testIsStringList(array $list): void
    {
        Assert::checkIsStringList($list);

        $this->assertTrue(true);
    }

    /**
     * @dataProvider negativeCheckIsStringListDataProvider
     * @expectedException \Esemve\Collection\Exception\InvalidTypeException
     */
    public function testIsStringListException(array $list): void
    {
        Assert::checkIsStringList($list);
    }

    /**
     * @dataProvider positiveCheckIsIntListDataProvider
     */
    public function testIsIntList(array $list): void
    {
        Assert::checkIsIntList($list);

        $this->assertTrue(true);
    }

    /**
     * @dataProvider negativeCheckIsIntListDataProvider
     * @expectedException \Esemve\Collection\Exception\InvalidTypeException
     */
    public function testIsIntListException(array $list): void
    {
        Assert::checkIsIntList($list);
    }

    /**
     * @dataProvider positiveCheckInstanceDataProvider
     */
    public function testCheckInstance($object, $instance): void
    {
        Assert::checkInstance($object, $instance);

        $this->assertTrue(true);
    }

    /**
     * @dataProvider negativeCheckInstanceDataProvider
     * @expectedException \Esemve\Collection\Exception\InvalidTypeException
     */
    public function testCheckInstanceException($object, $instance): void
    {
        Assert::checkInstance($object, $instance);
    }

    /**
     * @dataProvider positiveCheckListInstanceDataProvider
     */
    public function testCheckListInstance($object, $instance): void
    {
        Assert::checkListInstance($object, $instance);

        $this->assertTrue(true);
    }

    /**
     * @dataProvider negativeCheckListInstanceDataProvider
     * @expectedException \Esemve\Collection\Exception\InvalidTypeException
     */
    public function testCheckListInstanceException($object, $instance): void
    {
        Assert::checkListInstance($object, $instance);
    }

    public function positiveCheckIsStringListDataProvider(): array
    {
        return [
            [['a', 'b', 'c']],
            [['x', null, 'c']],
            [[]],
        ];
    }

    public function negativeCheckIsStringListDataProvider(): array
    {
        return [
            [[1, 2, 3]],
            [[0.0, 0.3]],
            [[new \stdClass()]],
        ];
    }

    public function positiveCheckIsIntListDataProvider(): array
    {
        return [
            [[1, 2, 3, 4]],
            [[1]],
            [[3, 1, null]],
        ];
    }

    public function negativeCheckIsIntListDataProvider(): array
    {
        return [
            [['1', '2', '3']],
            [[0.0, 0.3]],
            [[new \stdClass()]],
        ];
    }

    public function positiveCheckInstanceDataProvider(): array
    {
        return [
            [new \stdClass(), \stdClass::class],
            [new Assert(), Assert::class],
        ];
    }

    public function negativeCheckInstanceDataProvider(): array
    {
        return [
            [new \stdClass(), Assert::class],
            [new Assert(), \stdClass::class],
        ];
    }

    public function positiveCheckListInstanceDataProvider(): array
    {
        return [
            [[new \stdClass(), new \stdClass()], \stdClass::class],
            [[new Assert(), new Assert()], Assert::class],
        ];
    }

    public function negativeCheckListInstanceDataProvider(): array
    {
        return [
            [[new \stdClass()], Assert::class],
            [[new Assert()], \stdClass::class],
            [[new \stdClass(), new Assert()], \stdClass::class],
        ];
    }
}
