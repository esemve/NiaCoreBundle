<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Traits;

trait SerializableEntityTrait
{
    protected function getSerializableArray(): array
    {
        return [];
    }

    protected function getSerializeArray(array $serializable): array
    {
        return $serializable;
    }

    protected function parseSerializedArray(array $serialized): void
    {
        foreach ($serialized as $property => $value) {
            $this->{$property} = $value;
        }
    }

    public function serialize(): string
    {
        if (!empty($this->getSerializableArray())) {
            return serialize($this->getSerializableArray());
        }
        $serializable = [];
        foreach (get_object_vars($this) as $property => $value) {
            try {
                if ((!\is_object($value)) && (!\is_callable($value))) {
                    serialize($value);
                    $serializable[$property] = $value;
                }
            } catch (\Exception $e) {
            }
        }
        $array = $this->getSerializeArray($serializable);

        return serialize($array);
    }

    public function unserialize($serialized): void
    {
        $array = unserialize($serialized);

        $this->parseSerializedArray($array);
    }
}
