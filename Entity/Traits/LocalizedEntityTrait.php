<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Nia\CoreBundle\Entity\LocalizedEntityInterface;
use Nia\CoreBundle\Manager\AbstractManager;
use Nia\CoreBundle\ValueObject\Locale;

trait LocalizedEntityTrait
{
    /**
     * @var AbstractManager
     */
    protected $manager;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=2)
     */
    protected $locale;

    public function getLocale(): ?Locale
    {
        return $this->manager->getLocaleProvider()->provideByKey($this->locale);
    }

    public function setLocale(Locale $locale): LocalizedEntityInterface
    {
        $this->locale = $locale->getCode();

        return $this;
    }
}
