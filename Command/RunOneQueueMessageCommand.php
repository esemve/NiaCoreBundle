<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Command;

use Nia\CoreBundle\Entity\Queue;
use Nia\CoreBundle\Event\QueueEvent;
use Nia\CoreBundle\Manager\QueueManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RunOneQueueMessageCommand extends AbstractCommand
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

    public function setQueueManager(QueueManager $queueManager): void
    {
        $this->queueManager = $queueManager;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function configure()
    {
        $this
            ->setName('nia:queue:run')
            ->addArgument('id', InputArgument::REQUIRED, 'Queue message id')
            ->setDescription('Run one queue message');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $queue = $this->queueManager->findById($input->getArgument('id'));

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
            } catch (\Exception $ex) {
                $this->logError($queue, $ex, $output);
            }
        }
    }

    private function parse(Queue $queue, OutputInterface $output): void
    {
        $queue->setSuccess(new \DateTimeImmutable('now'));

        $output->writeln(
            sprintf('Message %s type %s success', $queue->getId(), \get_class($queue->getMessage()))
        );

        $this->queueManager->store($queue);
    }

    private function logError(Queue $queue, \Exception $ex, OutputInterface $output): void
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

        dump('Exception:', $ex, 'Queue:', $queue);

        $this->queueManager->store($queue);
    }
}
