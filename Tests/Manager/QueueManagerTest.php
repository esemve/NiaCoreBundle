<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Tests\Manager;

use Nia\CoreBundle\Entity\Manager\EntityManager;
use Nia\CoreBundle\Manager\QueueManager;
use Nia\CoreBundle\Test\DBTestCase;
use Nia\CoreBundle\ValueObject\TestQueueMessage;
use PHPUnit\DbUnit\DataSet\ArrayDataSet;
use PHPUnit\DbUnit\DataSet\IDataSet;

class QueueManagerTest extends DBTestCase
{
    public function testQueue(): void
    {
        $queueMessage = new TestQueueMessage();
        $queueMessage->setText('test message content');

        $manager = $this->createManager();

        $this->assertSame(0, $manager->getCountNotStarted());

        $manager->push($queueMessage);

        $this->assertSame(1, $manager->getCountNotStarted());
        $this->assertSame(0, $manager->getCountSuccess());

        $pop = $manager->pop();
        $this->assertSame($queueMessage->getSerialized(), $pop->getMessage()->getSerialized());

        $pop->setSuccess(new \DateTimeImmutable('now'));
        $manager->store($pop);

        $this->assertSame(1, $manager->getCountSuccess());

        $manager->removeSuccessMessages();

        $this->assertSame(0, $manager->getCountSuccess());
        $this->assertSame(0, $manager->getCountFail());

        $queueMessage = new TestQueueMessage();
        $queueMessage->setText('test message content 2');
        $manager->push($queueMessage);
        $this->assertSame(1, $manager->getCountNotStarted());
        $pop = $manager->pop();
        $manager->remove($pop);
        $this->assertSame(0, $manager->getCountNotStarted());
    }

    public function testFindById(): void
    {
        $queueMessage = new TestQueueMessage();
        $queueMessage->setText('test message content');

        $manager = $this->createManager();
        $manager->push($queueMessage);

        $queue = $manager->pop();
        $this->assertSame($queue->getId(), $manager->findById($queue->getId())->getId());
    }

    /**
     * Returns the test dataset.
     *
     * @return IDataSet
     */
    protected function getDataSet()
    {
        return new ArrayDataSet(require __DIR__.'/fixtures/queueManagerTestDataSet.php');
    }

    protected function createManager(): QueueManager
    {
        return $this->getContainer()->get('nia.core.queue.manager');
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->getContainer()->get('nia.core.entity.manager');
    }
}
