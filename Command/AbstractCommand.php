<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Command;

use Nia\CoreBundle\Security\Context;
use Nia\CoreBundle\Security\Factory\ContextFactory;
use Symfony\Component\Console\Command\Command;

class AbstractCommand extends Command
{
    /**
     * @var ContextFactory
     */
    private $contextFactory;

    public function setContextFactory(ContextFactory $contextFactory): void
    {
        $this->contextFactory = $contextFactory;
    }

    public function getServiceContext(self $caller, string $withId = null): Context
    {
        if (empty($withId)) {
            return $this->contextFactory->createServiceContext(\get_class($caller).':'.debug_backtrace()[1]['function']);
        }

        return $this->contextFactory->createServiceContext($withId);
    }
}
