<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Test;

use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Entity\IdentifiableEntityInterface;
use Nia\CoreBundle\Manager\AbstractManager;
use Nia\UserBundle\Entity\AbstractUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class QueryFilterTestCase extends TestCase
{
    abstract public function testIsSupported(): void;

    abstract public function testGetFilter(): void;

    abstract public function testPositiveCanEdit(): void;

    abstract public function testNegativeCanEdit(): void;

    abstract public function testPositiveCanDelete(): void;

    abstract public function testNegativeCanDelete(): void;

    abstract public function testPositiveCanCreate(): void;

    abstract public function testNegativeCanCreate(): void;

    protected function createMockEntity(?int $id = 1): Entity
    {
        $mockManager = $this->createMock(AbstractManager::class);
        $mockManager->expects($this->any())->method('getRoleGroup')->willReturn('TEST');

        $entity = $this->createMock(Entity::class);

        if (!empty($id)) {
            $entity = $this->createMock([Entity::class, IdentifiableEntityInterface::class]);
            $entity->expects($this->any())->method('getId')->willReturn($id);
        }

        $entity->expects($this->any())->method('getManager')->willReturn($mockManager);

        return $entity;
    }

    public function createTokenStorageWithRoles(array $roles, ?AbstractUser $user = null): TokenStorageInterface
    {
        $mockToken = $this->createMock(\Symfony\Component\Security\Core\Authentication\Token\TokenInterface::class);

        $mockRoles = [];

        foreach ($roles as $role) {
            $roleName = $role;

            if ('ROLE' !== mb_substr($role, 0, 4)) {
                $roleName = 'ROLE_TEST_'.$role;
            }

            $mockRoles[] = new \Symfony\Component\Security\Core\Role\Role($roleName);
        }
        $mockToken->expects($this->any())->method('getRoles')->willReturn($mockRoles);

        if (!empty($user)) {
            $mockToken->expects($this->any())->method('getUser')->willReturn($user);
        }

        $mock = $this->createMock(TokenStorageInterface::class);
        $mock->expects($this->any())->method('getToken')->willReturn($mockToken);

        return $mock;
    }
}
