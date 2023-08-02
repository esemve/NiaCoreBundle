<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class UnitTestToken extends AbstractToken
{
    public function __construct(array $roles = [], $user = null)
    {
        parent::__construct($roles);
        if (!empty($user)) {
            $this->setUser($user);
        }
    }

    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        return null;
    }
}
