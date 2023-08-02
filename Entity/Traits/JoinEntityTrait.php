<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Traits;

use Esemve\Collection\AbstractTypedCollection;
use Nia\CoreBundle\Entity\IdentifiableEntityInterface;

trait JoinEntityTrait
{
    /**
     * @var AbstractTypedCollection[]
     */
    protected $internalCollectionCache = [];

    protected function getJoinedEntities(string $fieldName, string $collectionClass): AbstractTypedCollection
    {
        if (true !== isset($this->internalCollectionCache[$fieldName])) {
            $this->internalCollectionCache[$fieldName] = new $collectionClass();
            $data = $this->{$fieldName};

            foreach ($data as $element) {
                $this->internalCollectionCache[$fieldName]->push($element);
            }
        }

        return $this->internalCollectionCache[$fieldName];
    }

    protected function addJoinedEntity(string $fieldName, IdentifiableEntityInterface $entity): self
    {
        if (true === isset($this->internalCollectionCache[$fieldName])) {
            if (null !== $entity->getId()) {
                $firstSameValue = $this->internalCollectionCache[$fieldName]->first(function ($item) use ($entity) {
                    return $item->getId() === $entity->getId();
                });

                if (empty($firstSameValue)) {
                    $this->internalCollectionCache[$fieldName]->push($entity);
                }
            }
        }

        if ((!isset($firstSameValue)) || (empty($firstSameValue))) {
            $this->{$fieldName}->add($entity);
        }

        return $this;
    }

    protected function replaceJoinedEntities(string $fieldName, AbstractTypedCollection $entities): self
    {
        $this->{$fieldName}->clear();

        foreach ($entities as $entity) {
            $this->addJoinedEntity($fieldName, $entity);
        }

        return $this;
    }

    protected function deleteJoinedEntity(string $fieldName, IdentifiableEntityInterface $entity): self
    {
        if (true === isset($this->internalCollectionCache[$fieldName])) {
            foreach ($this->internalCollectionCache[$fieldName] as $key => $element) {
                if (null !== $entity->getId()) {
                    if ($element->getId() === $entity->getId()) {
                        $this->internalCollectionCache[$fieldName]->forget($key);
                    }
                } else {
                    if ($element === $entity) {
                        $this->internalCollectionCache[$fieldName]->forget($key);
                    }
                }
            }
        }

        $this->{$fieldName}->removeElement($entity);

        return $this;
    }
}
