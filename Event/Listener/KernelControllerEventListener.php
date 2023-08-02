<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Event\Listener;

use Nia\CoreBundle\Controller\AbstractController;
use Nia\CoreBundle\Controller\AbstractServiceController;
use Nia\CoreBundle\Provider\LocaleProvider;
use Nia\CoreBundle\ValueObject\Locale;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class KernelControllerEventListener
{
    /**
     * @var LocaleProvider
     */
    private $localeProvider;
    /**
     * @var string
     */
    private $env;

    public function __construct(LocaleProvider $localeProvider, string $env)
    {
        $this->localeProvider = $localeProvider;
        $this->env = $env;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!\in_array($this->env, ['prod', 'dev'], true)) {
            return;
        }

        if (!$event->getController()[0] instanceof AbstractController) {
            return;
        }

        if ($event->getController()[0] instanceof AbstractServiceController) {
            return;
        }

        $uri = trim($event->getRequest()->getPathInfo(), '/');

        $locales = $this->localeProvider->getAvailableLocales();
        $uriParts = explode('/', $uri);

        /** @var Locale $locale */
        $locale = $locales->get($uriParts[0], $this->localeProvider->getDefaultLocale());

        $event->getRequest()->setDefaultLocale($this->localeProvider->getDefaultLocale()->getCode());
        $event->getRequest()->getSession()->set('_locale', $locale->getCode());
        $event->getRequest()->setLocale($locale->getCode());
    }
}
