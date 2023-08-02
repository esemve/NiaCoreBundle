<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity;

interface LoggedEntityInterface
{
    public function setCreatedBy(?EntityReference $reference = null): Entity;

    public function setUpdatedBy(?EntityReference $reference = null): Entity;

    public function getCreatedBy(): EntityReference;

    public function getUpdatedBy(): EntityReference;
}
