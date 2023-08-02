<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Factory;

use Nia\CoreBundle\Entity\EntityReference;

interface EntityReferenceFactoryInterface
{
    /**
     * Átadott entity class név és id alapján egy referenciát készít.
     *
     * @param string $class
     * @param int    $id
     *
     * @return EntityReference
     */
    public function create(string $class, int $id): EntityReference;
}
