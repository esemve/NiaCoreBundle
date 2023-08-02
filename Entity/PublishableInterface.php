<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity;

interface PublishableInterface
{
    public function setPublicFrom(?\DateTime $fromDate = null): Entity;

    public function setPublicTo(?\DateTime $toDate = null): Entity;

    public function getPublicFrom(): ?\DateTime;

    public function getPublicTo(): ?\DateTime;
}
