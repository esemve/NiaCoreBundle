<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Security;

use Nia\CoreBundle\ValueObject\Locale;
use Nia\UserBundle\Entity\AbstractUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class Context
{
    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * @var Locale
     */
    private $locale;
    /**
     * @var string
     */
    private $env;
    /**
     * @var array
     */
    private $temporaryRoles;

    public function __construct(TokenInterface $token, Locale $locale, string $env, array $temporaryRoles)
    {
        $this->token = $token;
        $this->locale = $locale;
        $this->env = $env;
        $this->temporaryRoles = $temporaryRoles;
    }

    public function hasRole(string $role): bool
    {
        if (\in_array($role, $this->token->getRoleNames(), true)) {
            return true;
        }

        if (\in_array($role, $this->temporaryRoles, true)) {
            return true;
        }

        return false;
    }

    public function getUser(): ?AbstractUser
    {
        $user = $this->token->getUser();

        if (!\is_object($user)) {
            return null;
        }

        if ($user instanceof AbstractUser) {
            return $user;
        }

        return null;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function getEnv(): string
    {
        return $this->env;
    }

    public function isDev(): bool
    {
        return 'admin_dev' === $this->env || 'dev' === $this->env;
    }

    public function getToken(): TokenInterface
    {
        return $this->token;
    }

    public function getRoles(): array
    {
        $roles = $this->token->getRoleNames();

        return array_merge($roles, $this->temporaryRoles);
    }
}
