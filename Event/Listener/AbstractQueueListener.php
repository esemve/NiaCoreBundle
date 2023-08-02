<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Event\Listener;

use Nia\CoreBundle\Event\QueueEvent;
use Nia\CoreBundle\ValueObject\QueueMessageInterface;

abstract class AbstractQueueListener extends AbstractListener
{
    abstract protected function isSupported(QueueMessageInterface $message): bool;

    abstract public function process(QueueEvent $event): void;

    public static function getSubscribedEvents()
    {
        return [
            QueueEvent::EVENT => [['startProcess', 20]],
        ];
    }

    public function startProcess(QueueEvent $event): void
    {
        if ($this->isSupported($event->getMessage())) {
            $this->process($event);
            $event->getMessage()->setProcessed();
        }
    }
}
