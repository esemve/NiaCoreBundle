<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Factory;

use Nia\CoreBundle\ValueObject\SimpleMailQueueMessage;

class MailQueueMessageFactory
{
    public function createSimpleMailQueueMessage(string $subject, string $recipient, string $html, string $txt): SimpleMailQueueMessage
    {
        return new SimpleMailQueueMessage(
           $subject,
           $recipient,
           $html,
           $txt
        );
    }
}
