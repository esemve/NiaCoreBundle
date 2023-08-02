<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Service;

use Nia\CoreBundle\Entity\Log;
use Nia\CoreBundle\Entity\Manager\EntityManager;
use Nia\CoreBundle\Enum\Factory\EnumFactory;
use Nia\CoreBundle\Enum\LogEventEnum;
use Nia\UserBundle\Entity\AbstractUser;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NiaLogger
{
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var EnumFactory
     */
    private $enumFactory;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var array
     */
    private $notLoggable;

    public function __construct(EntityManager $entityManager, RequestStack $requestStack, TokenStorageInterface $tokenStorage, EnumFactory $enumFactory, array $notLoggable)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->enumFactory = $enumFactory;
        $this->tokenStorage = $tokenStorage;
        $this->notLoggable = $notLoggable;
    }

    public function logCreate(string $target, string $info = null): void
    {
        if (!$this->isLoggable($target)) {
            return;
        }

        /** @var Log $log */
        $log = $this->createEntity($target, $info);
        $log->setEvent($this->enumFactory->create(LogEventEnum::class, LogEventEnum::CREATE));
        $this->entityManager->persist($log);
        $this->entityManager->flush($log);
    }

    public function logEdit(string $target, string $info = null): void
    {
        if (!$this->isLoggable($target)) {
            return;
        }

        /** @var Log $log */
        $log = $this->createEntity($target, $info);
        $log->setEvent($this->enumFactory->create(LogEventEnum::class, LogEventEnum::CREATE));
        $this->entityManager->persist($log);
        $this->entityManager->flush($log);
    }

    public function logDelete(string $target, string $info = null): void
    {
        if (!$this->isLoggable($target)) {
            return;
        }

        /** @var Log $log */
        $log = $this->createEntity($target, $info);
        $log->setEvent($this->enumFactory->create(LogEventEnum::class, LogEventEnum::DELETE));
        $this->entityManager->persist($log);
        $this->entityManager->flush($log);
    }

    public function logLogin(string $target, ?string $info): void
    {
        if (!$this->isLoggable($target)) {
            return;
        }

        /** @var Log $log */
        $log = $this->createEntity($target, $info);
        $log->setEvent($this->enumFactory->create(LogEventEnum::class, LogEventEnum::LOG_IN));
        $this->entityManager->persist($log);
        $this->entityManager->flush($log);
    }

    public function logSend(string $target, string $info = null): void
    {
        /** @var Log $log */
        $log = $this->createEntity($target, $info);
        $log->setEvent($this->enumFactory->create(LogEventEnum::class, LogEventEnum::SEND));
        $this->entityManager->persist($log);
        $this->entityManager->flush($log);
    }

    public function logStart(string $target, string $info = null): void
    {
        if (!$this->isLoggable($target)) {
            return;
        }

        /** @var Log $log */
        $log = $this->createEntity($target, $info);
        $log->setEvent($this->enumFactory->create(LogEventEnum::class, LogEventEnum::START));
        $this->entityManager->persist($log);
        $this->entityManager->flush($log);
    }

    public function logStop(string $target, string $info = null): void
    {
        if (!$this->isLoggable($target)) {
            return;
        }

        /** @var Log $log */
        $log = $this->createEntity($target, $info);
        $log->setEvent($this->enumFactory->create(LogEventEnum::class, LogEventEnum::STOP));
        $this->entityManager->persist($log);
        $this->entityManager->flush($log);
    }

    public function logShow(string $target, string $info = null): void
    {
        if (!$this->isLoggable($target)) {
            return;
        }

        /** @var Log $log */
        $log = $this->createEntity($target, $info);
        $log->setEvent($this->enumFactory->create(LogEventEnum::class, LogEventEnum::SHOW));
        $this->entityManager->persist($log);
        $this->entityManager->flush($log);
    }

    public function logUpload(string $target, string $info = null): void
    {
        if (!$this->isLoggable($target)) {
            return;
        }

        /** @var Log $log */
        $log = $this->createEntity($target, $info);
        $log->setEvent($this->enumFactory->create(LogEventEnum::class, LogEventEnum::UPLOAD));
        $this->entityManager->persist($log);
        $this->entityManager->flush($log);
    }

    public function log(string $target, string $info = null): void
    {
        if (!$this->isLoggable($target)) {
            return;
        }
        /** @var Log $log */
        $log = $this->createEntity($target, $info);
        $log->setEvent($this->enumFactory->create(LogEventEnum::class, LogEventEnum::INFO));
        $this->entityManager->persist($log);
        $this->entityManager->flush($log);
    }

    private function createEntity(string $target, ?string $info): Log
    {
        /** @var Log $log */
        $log = new Log();
        $log->setCreatedAt(new \DateTime());
        if ($this->requestStack->getMasterRequest()) {
            $userId = null;
            if ($this->tokenStorage->getToken()->getUser() instanceof AbstractUser) {
                $userId = $this->tokenStorage->getToken()->getUser()->getId();
            }
            $log->setIp($this->requestStack->getMasterRequest()->getClientIp() ?? '');
            $log->setUserId($userId);
            $log->setUrl($this->requestStack->getMasterRequest()->getRequestUri() ?? '');
        }
        $log->setInfo($info);
        $log->setTarget($target);

        return $log;
    }

    private function isLoggable(string $target): bool
    {
        foreach ($this->notLoggable as $string) {
            if (mb_substr($target, 0, mb_strlen($string)) === $string) {
                return false;
            }
        }

        return true;
    }
}
