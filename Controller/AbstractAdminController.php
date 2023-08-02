<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Controller;

use Nia\AdminBundle\Utils\AdminBreadcrumb;
use Nia\CoreBundle\Service\NiaLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractAdminController extends AbstractController
{
    public function __construct()
    {
        $refl = new \ReflectionClass(\get_called_class());

        foreach ($refl->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ('Action' === mb_substr($method->getName(), -6)) {
                if ((null === $method->getDocComment()) || (mb_strpos((string) $method->getDocComment(), '@Security') < 1)) {
                    throw new \Exception(sprintf('%s method in %s not contains security annotation!', $method->getName(), \get_called_class()));
                }
            }
        }
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->container->get('event_dispatcher');
    }

    protected function getRowCountPerPage(Request $request): int
    {
        return 50;
    }

    protected function getBreadcrumb(): AdminBreadcrumb
    {
        return $this->container->get('nia.admin.utils.admin_breadcrumb');
    }

    protected function getFixedRolePrefix(): string
    {
        if (!empty(static::ROLE_PREFIX)) {
            return static::ROLE_PREFIX.'_';
        }

        throw new \Exception('Not found any ROLE_PREFIX!');
    }

    protected function setSuccessFlash(?string $message = null): void
    {
        $this->setFlashMessage(
            'success',
            $message ?? $this->getTranslator()->trans('NiaAdminBundle@message.success_save')
        );
    }

    protected function setSuccessDeleteFlash(?string $message = null): void
    {
        $this->setFlashMessage(
            'success',
            $message ?? $this->getTranslator()->trans('NiaAdminBundle@message.success_delete')
        );
    }

    protected function setErrorFlash(?string $message = null): void
    {
        $this->setFlashMessage(
            'error',
            $message ?? $this->getTranslator()->trans('NiaAdminBundle@message.success_error')
        );
    }

    public function getNiaLogger(): NiaLogger
    {
        return $this->container->get('nia.core.logger');
    }
}
