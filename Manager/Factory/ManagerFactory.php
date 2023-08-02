<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Manager\Factory;

use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Exception\InvalidConfigrationException;
use Symfony\Component\DependencyInjection\Container;

class ManagerFactory
{
    /**
     * @var array
     */
    private $managerMapping;
    /**
     * @var Container
     */
    private $container;

    public function __construct(array $managerMapping, Container $container)
    {
        $this->managerMapping = $managerMapping;
        $this->container = $container;
    }

    public function setManagerMapping(array $managerMapping): void
    {
        $this->managerMapping = $managerMapping;
    }

    public function create(string $entityClass)
    {
        return $this->container->get($this->getManagerServiceName($entityClass));
    }

    public function createByEntity(Entity $entity)
    {
        return $this->container->get($this->getManagerServiceName(\get_class($entity)));
    }

    private function getManagerServiceName(string $entityClass)
    {
        $managerServiceName = array_search($entityClass, $this->managerMapping, true);

        if (false === $managerServiceName) {
            throw new InvalidConfigrationException(
                sprintf('Not found any configured manager for the % entity!', $entityClass)
            );
        }

        return $managerServiceName;
    }
}
