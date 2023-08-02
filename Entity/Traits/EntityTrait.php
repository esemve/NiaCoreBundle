<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Traits;

use Nia\CoreBundle\Manager\AbstractManager;

trait EntityTrait
{
    /**
     * @var AbstractManager
     */
    protected $manager;

    public function setManager(AbstractManager $manager): void
    {
        $this->manager = $manager;
    }

    public function getManager(): AbstractManager
    {
        return $this->manager;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function toArrayWithoutManager(): array
    {
        $array = get_object_vars($this);
        unset($array['manager']);

        return $array;
    }
}
