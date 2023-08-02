<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Nia\CoreBundle\Entity\Queue;
use Nia\CoreBundle\Event\QueueEvent;
use Nia\CoreBundle\Manager\QueueManager;
use Nia\CoreBundle\Manager\RunLogManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class QueueWorkerCommand extends AbstractCommand
{
    /**
     * @var QueueManager
     */
    protected $queueManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    protected $startTime;

    /**
     * @var RunLogManager
     */
    protected $runLogManager;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function setRunLogManager(RunLogManager $runLogManager): void
    {
        $this->runLogManager = $runLogManager;
    }

    public function setQueueManager(QueueManager $queueManager): void
    {
        $this->queueManager = $queueManager;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName('nia:queue:worker')
            ->setDescription('Run queue workers.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->runLogManager->update('queue');

        $this->startTime = new \DateTime('now');

        // Ha több workert indítunk legalább ne ugyanabban a milisecben akarjanak dolgozni
        usleep(rand(1, 100000));

        if (!$output->isVerbose()) {
            $this->queueManager->removeSuccessMessages();
        }

        $i = 0;
        $logEvery = 60 - rand(0, 20);

        while ($i < 300) {
            if (0 === $i % $logEvery) {
                $this->runLogManager->update('queue');
            }

            $this->showRoundInfo($i, $output);

            $queue = $this->queueManager->pop();

            if (null !== $queue) {
                try {
                    $event = new QueueEvent($queue->getMessage());

                    $this->eventDispatcher->dispatch(
                        QueueEvent::EVENT,
                        $event
                    );

                    if (!$event->getMessage()->isProcessed()) {
                        throw new \Exception(sprintf('Not found any processor for %s type!', $event->getType()));
                    }

                    $this->parse($queue, $output);
                } catch (\Throwable $ex) {
                    $this->logError($queue, $ex, $output);
                }

                ++$i;
                $this->entityManager->clear();
                // Volt meló szóval ugrunk rögtön a kövire, hátha van még
                continue;
            }

            sleep(1);
            ++$i;
        }
    }

    private function showRoundInfo(int $i, OutputInterface $output): void
    {
        if ($output->isVerbose()) {
            $output->writeln(
                sprintf(
                    'Queue worker %s round #%s at %s',
                    $this->startTime->format('Y-m-d H:i:s'),
                    $i,
                    date('Y-m-d H:i:s')
                )
            );
        }
    }

    private function parse(Queue $queue, OutputInterface $output): void
    {
        $queue->setSuccess(new \DateTimeImmutable('now'));

        if ($output->isVerbose()) {
            $output->writeln(
                sprintf('Message %s type %s success', $queue->getId(), \get_class($queue->getMessage()))
            );
        }

        $this->queueManager->store($queue);
    }

    private function logError(Queue $queue, \Throwable $ex, OutputInterface $output): void
    {
        $queue->setError(json_encode(
            [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'log' => $ex->getTrace(),
                'line' => $ex->getLine(),
            ]
        ));

        $queue->setFailCount($queue->getFailCount() + 1);

        $queue->setProcessableStart(new \DateTimeImmutable(date('Y-m-d H:i:s', strtotime('+'.($queue->getFailCount() * rand(30, 60)).' secs'))));
        $queue->setLocked(null);

        if ($output->isVerbose()) {
            dump('Exception:', $ex, 'Queue:', $queue);
        }

        $this->queueManager->store($queue);
    }
}
