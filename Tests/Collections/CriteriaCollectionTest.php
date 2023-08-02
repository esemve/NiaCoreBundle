<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Manager;

use Esemve\Tests\Collection\AbstractCollectionTestCase;
use Nia\CoreBundle\Collections\CriteriaCollection;

class CriteriaCollectionTest extends AbstractCollectionTestCase
{
    /**
     * Name of the tested Collection class.
     *
     * @return string
     */
    protected function getClassName(): string
    {
        return CriteriaCollection::class;
    }

    /**
     * Dataprovider for positive tests.
     *
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            [[['first', '=', 'aa']], ['a', '<', 3]],
            [[['a', '<', 3]], ['a', '<', 3]],
            [[['asdsa', '>', 4]], ['a', '<', 3]],
            [[['xxx', '<=', 'sda']], ['a', '<', 3]],
            [[['verse', '>=', 'lldlsa']], ['a', '<', 3]],
            [[['dsakmdsa.dasda', '!=', 'dsadas.d  dsad sad d']], ['a', '<', 3]],
            [[['aa', '<>', 'bb']], ['a', '<', 3]],
            [[['test', 'IN', 'info']], ['a', '<', 3]],
            [[['a', 'NOT IN', 'xx']], ['a', '<', 3]],
            [[['xxx', 'NOT LIKE', 'yyyy']], ['a', '<', 3]],
            [[['dddd', 'LIKE', 'dadsdasda']], ['a', '<', 3]],
            [[['tttt', 'LIKE%', 'yyyy']], ['a', '<', 3]],
            [[['ssss', '%LIKE', 'dsadsa']], ['a', '<', 3]],
            [[['uuu', '%LIKE%', 'fff']], ['a', '<', 3]],
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
            [['first', 'ttt', 'aa']],
            [['first', '>>', 'aa']],
            [['first', '<<', 'aa']],
            [['first', 'LIK', 'aa']],
        ];
    }
}
