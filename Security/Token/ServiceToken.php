<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class ServiceToken extends AbstractToken
{
    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        return '';
    }
}
