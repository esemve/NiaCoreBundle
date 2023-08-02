<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nia\CoreBundle\Entity\Traits\EntityTrait;
use Nia\CoreBundle\Entity\Traits\IdentifiableEntityTrait;

/**
 * Migrations.
 *
 * @ORM\Table(name="migrations")
 * @ORM\Entity(repositoryClass="Nia\CoreBundle\Repository\MigrationsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Migration implements Entity, IdentifiableEntityInterface
{
    use IdentifiableEntityTrait;
    use EntityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="migration", type="string", length=255)
     */
    protected $migration;

    /**
     * @return string
     */
    public function getMigration(): string
    {
        return $this->migration;
    }

    public function setMigration(string $migration): self
    {
        $this->migration = $migration;

        return $this;
    }
}
