<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Twig\Extension;

use Nia\CoreBundle\Event\Listener\AbstractListener;
use Nia\CoreBundle\Security\Context;
use Nia\CoreBundle\Security\Factory\ContextFactory;

abstract class AbstractTwigExtension extends \Twig_Extension
{
    /**
     * @var ContextFactory
     */
    private $contextFactory;

    public function setContextFactory(ContextFactory $contextFactory): void
    {
        $this->contextFactory = $contextFactory;
    }

    public function getContext(): Context
    {
        return $this->contextFactory->create();
    }

    public function getServiceContext(AbstractListener $caller): Context
    {
        return $this->contextFactory->createServiceContext(\get_class($caller).':'.debug_backtrace()[1]['function']);
    }
}
