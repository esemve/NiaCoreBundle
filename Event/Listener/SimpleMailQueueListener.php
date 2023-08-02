<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Event\Listener;

use Nia\CoreBundle\Event\QueueEvent;
use Nia\CoreBundle\Utils\MailSender;
use Nia\CoreBundle\ValueObject\QueueMessageInterface;
use Nia\CoreBundle\ValueObject\SimpleMailQueueMessage;

class SimpleMailQueueListener extends AbstractQueueListener
{
    /**
     * @var MailSender
     */
    protected $mailer;

    public function setMailer(MailSender $mailSender): void
    {
        $this->mailer = $mailSender;
    }

    protected function isSupported(QueueMessageInterface $message): bool
    {
        return $message instanceof SimpleMailQueueMessage;
    }

    public function process(QueueEvent $event): void
    {
        /** @var SimpleMailQueueMessage $message */
        $message = $event->getMessage();

        $this->mailer->send(
            $message->getEmail(),
            $message->getSubject(),
            $message->getHtml(),
            $message->getTxt()
        );
    }
}
