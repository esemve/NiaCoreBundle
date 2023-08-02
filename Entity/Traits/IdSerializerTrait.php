<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Traits;

use Nia\CoreBundle\Manager\Factory\ManagerFactory;

trait IdSerializerTrait
{
    public function serialize(): string
    {
        $array = ['id' => $this->getId()];

        return serialize($array);
    }

    public function unserialize($serialized): void
    {
        $id = unserialize($serialized)['id'];
        $this->manager = ManagerFactory::createByEntity($this);

        $fromDb = $this->manager->findById($id);

        $reflectFromDb = new \ReflectionClass($fromDb);

        foreach ($reflectFromDb->getProperties() as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);
            $this->{$reflectionProperty->getName()} = $reflectionProperty->getValue($fromDb);
        }
    }
}
