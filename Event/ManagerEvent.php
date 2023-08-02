<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Event;

use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Enum\IsNewEnum;
use Nia\CoreBundle\Security\Context;
use Symfony\Component\EventDispatcher\Event;

class ManagerEvent extends Event
{
    const BEFORE_SAVE = 'nia.manager.beforeSave';

    const AFTER_SAVE = 'nia.manager.afterSave';

    const SUCCESS_SAVE = 'nia.manager.successSave';

    const ERROR_SAVE = 'nia.manager.errorSave';

    const BEFORE_REMOVE = 'nia.manager.beforeRemove';

    const AFTER_REMOVE = 'nia.manager.beforeRemove';

    const SUCCESS_REMOVE = 'nia.manager.successRemove';

    const ERROR_REMOVE = 'nia.manager.errorRemove';

    const BEFORE_FORCE_REMOVE = 'nia.manager.beforeForceRemove';

    const AFTER_FORCE_REMOVE = 'nia.manager.beforeForceRemove';

    const SUCCESS_FORCE_REMOVE = 'nia.manager.successForceRemove';

    const ERROR_FORCE_REMOVE = 'nia.manager.errorForceRemove';

    const BEFORE_SOFTDELETE = 'nia.manager.beforeSoftDelete';

    const AFTER_SOFTDELETE = 'nia.manager.afterSoftDelete';

    const SUCCESS_SOFTDELETE = 'nia.manager.successSoftDelete';

    const ERROR_SOFTDELETE = 'nia.manager.errorSoftDelete';

    protected $entity;
    /**
     * @var IsNewEnum|null
     */
    private $isNewEnum;
    /**
     * @var Context
     */
    private $context;

    public function __construct(Entity $entity, ?IsNewEnum $isNewEnum, Context $context)
    {
        $this->entity = $entity;
        $this->isNewEnum = $isNewEnum;
        $this->context = $context;
    }

    public function getEntity(): Entity
    {
        return $this->entity;
    }

    public function isNew(): ?IsNewEnum
    {
        return $this->isNewEnum;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
