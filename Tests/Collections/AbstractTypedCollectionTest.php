<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: smv
 * Date: 2019.03.10.
 * Time: 17:04.
 */

namespace Nia\CoreBundle\Tests\Collections;

use Esemve\Tests\Collection\AbstractCollectionTestCase;

abstract class AbstractTypedCollectionTest extends AbstractCollectionTestCase
{
    /**
     * Dataprovider for positive tests.
     *
     * @return array
     */
    public function dataProvider(): array
    {
        $mock = $this->createMock($this->getCollectedTypeClass());

        return [
            [[$mock], $mock],
        ];
    }

    /**
     * Dataprovider for negative tests.
     *
     * @return array
     */
    public function exceptionDataProvider(): array
    {
        return [
            [1],
            [new \stdClass()],
            [function () {
            }],
            [[1, 2, 3]],
            [['test', 'info']],
        ];
    }

    abstract public function getCollectedTypeClass(): string;
}
