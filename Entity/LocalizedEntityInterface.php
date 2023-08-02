<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity;

use Nia\CoreBundle\ValueObject\Locale;

interface LocalizedEntityInterface
{
    public function getLocale(): ?Locale;

    public function setLocale(Locale $locale): self;
}
