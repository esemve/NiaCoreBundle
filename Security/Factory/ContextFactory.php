<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Security\Factory;

use Nia\CoreBundle\Exception\AccessDeniedException;
use Nia\CoreBundle\Provider\LocaleProvider;
use Nia\CoreBundle\Security\Context;
use Nia\CoreBundle\Security\Token\EmptyToken;
use Nia\CoreBundle\Security\Token\ServiceToken;
use Nia\CoreBundle\ValueObject\Locale;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ContextFactory
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var LocaleProvider
     */
    private $localeProvider;
    /**
     * @var string
     */
    private $environment;
    /**
     * @var array
     */
    private $serviceRoles;

    public function __construct(TokenStorageInterface $tokenStorage, RequestStack $requestStack, LocaleProvider $localeProvider, string $environment, array $serviceRoles)
    {
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->localeProvider = $localeProvider;
        $this->environment = $environment;
        $this->serviceRoles = $serviceRoles;
    }

    public function create(?TokenInterface $token = null, ?Locale $locale = null, ?string $environment = null, ?array $temporaryRoles = []): Context
    {
        if (null === $token) {
            $token = $this->tokenStorage->getToken();
        }

        if (null === $token) {
            $token = new EmptyToken();
        }

        return new Context($token, $locale ?? $this->localeProvider->getCurrentLocale(), $environment ?? $this->environment, $temporaryRoles);
    }

    public function createServiceContext(string $serviceContextName): Context
    {
        if ((!isset($this->serviceRoles[$serviceContextName])) || (!isset($this->serviceRoles[$serviceContextName]['roles']))) {
            throw new AccessDeniedException(sprintf('Not found valid configuration for this service context: %s', $serviceContextName));
        }

        return $this->create(new ServiceToken($this->serviceRoles[$serviceContextName]['roles']));
    }
}
