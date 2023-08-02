<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Event;

use Nia\CoreBundle\ValueObject\QueueMessageInterface;
use Symfony\Component\EventDispatcher\Event;

class QueueEvent extends Event
{
    const EVENT = 'process';

    /**
     * @var QueueMessageInterface
     */
    private $queueMessage;

    public function __construct(QueueMessageInterface $queueMessage)
    {
        $this->queueMessage = $queueMessage;
    }

    public function getMessage(): QueueMessageInterface
    {
        return $this->queueMessage;
    }

    public function getType(): string
    {
        return \get_class($this->queueMessage);
    }
}
