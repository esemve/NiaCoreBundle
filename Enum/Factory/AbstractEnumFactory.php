<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Enum\Factory;

use Esemve\Collection\Collection;
use Esemve\Collection\CollectionFactoryInterface;
use Esemve\Collection\StringCollection;
use Nia\CoreBundle\Enum\AbstractEnum;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractEnumFactory
{
    /**
     * @var StringCollection
     */
    private $enumOverride;
    /**
     * @var CollectionFactoryInterface
     */
    private $collectionFactory;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * EnumFactory constructor.
     *
     * @param array                      $enumOverride
     * @param CollectionFactoryInterface $collectionFactory
     * @param TranslatorInterface        $translator
     */
    public function __construct(array $enumOverride, CollectionFactoryInterface $collectionFactory, TranslatorInterface $translator)
    {
        $this->collectionFactory = $collectionFactory;
        $this->enumOverride = $collectionFactory->createStringCollection($enumOverride);
        $this->translator = $translator;
    }

    protected function getClass(string $class): string
    {
        if ($this->enumOverride->get($class)) {
            $class = $this->enumOverride->get($class);
        }

        return $class;
    }

    public function create(string $class, $value)
    {
        $class = $this->getClass($class);

        return new $class($value);
    }

    public function getKeys(string $class): Collection
    {
        $class = $this->getClass($class);

        return $this->collectionFactory->createCollection($class::keys());
    }

    public function getValues(string $class): Collection
    {
        $class = $this->getClass($class);

        return $this->collectionFactory->createCollection($class::values());
    }

    public function all(string $class): Collection
    {
        $class = $this->getClass($class);

        return $this->collectionFactory->createCollection($class::toArray());
    }

    public function getTranslationKeys(string $class): Collection
    {
        $originalClass = $this->getClass($class);

        $constantArray = [];

        $originalValues = $this->all($originalClass);
        foreach ($originalValues as $key => $value) {
            $constantArray[$key] = $value;
        }

        $class = new \ReflectionClass($originalClass);

        while (!(AbstractEnum::class === $class->getName())) {
            $bundleName = $class->getName();

            if ('Nia' === mb_substr($bundleName, 0, 3)) {
                $bundleName = explode('\\', $bundleName);
                $bundleName = 'Nia'.$bundleName[1];
            } else {
                $bundleName = explode('\\', $bundleName);
                $bundleName = $bundleName[0];
            }

            foreach ($class->getConstants() as $constant => $constantValue) {
                $key = $bundleName.'@enum.'.$class->getShortName().'.'.$constant;
                $constantArray[$constant] = $key;
            }

            $class = $class->getParentClass();
        }

        $output = [];

        foreach ($originalValues as $key => $value) {
            if (isset($constantArray[$key])) {
                $output[$value] = $constantArray[$key];
            }
        }

        return $this->collectionFactory->createCollection($output);
    }
}
