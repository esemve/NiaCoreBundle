<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Event\Listener;

use Nia\CoreBundle\Security\Context;
use Nia\CoreBundle\Security\Factory\ContextFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractListener implements EventSubscriberInterface
{
    /**
     * @var ContextFactory
     */
    private $contextFactory;

    public static function getSubscribedEvents()
    {
    }

    public function setContextFactory(ContextFactory $contextFactory): void
    {
        $this->contextFactory = $contextFactory;
    }

    public function getContext(): Context
    {
        return $this->contextFactory->create();
    }

    public function getServiceContext(self $caller): Context
    {
        return $this->contextFactory->createServiceContext(\get_class($caller).':'.debug_backtrace()[1]['function']);
    }
}
