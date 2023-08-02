<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Test;

use Esemve\Collection\CollectionFactoryInterface;
use Nia\CoreBundle\Entity\Entity;
use Nia\CoreBundle\Entity\Factory\EntityReferenceFactoryInterface;
use Nia\CoreBundle\Enum\Factory\EnumFactory;
use Nia\CoreBundle\Enum\StatusEnum;
use Nia\CoreBundle\Manager\AbstractManager;
use Nia\CoreBundle\Manager\Factory\ManagerFactory;
use Nia\CoreBundle\Security\Context;
use Nia\CoreBundle\Security\Factory\ContextFactory;
use Nia\CoreBundle\Security\Token\ServiceToken;
use Nia\CoreBundle\Security\UnitTestToken;
use Nia\CoreBundle\ValueObject\Locale;
use Nia\UserBundle\Entity\AbstractUser;
use Nia\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Yaml\Yaml;

abstract class TestCase extends WebTestCase
{
    protected static $_container;
    private $_resetToken = false;

    public function getContainer(): ContainerInterface
    {
        if (null === self::$_container) {
            self::$_container = static::bootKernel([])->getContainer();
        }

        return self::$_container;
    }

    protected function getCollectionFactory(): CollectionFactoryInterface
    {
        return $this->getContainer()->get('nia.core.collection.factory');
    }

    protected function getEnumFactory(): EnumFactory
    {
        return $this->getContainer()->get('nia.core.enum.factory');
    }

    protected function getManagerFactory(): ManagerFactory
    {
        return $this->getContainer()->get('nia.core.manager.factory');
    }

    protected function getEntityReferenceFactory(): EntityReferenceFactoryInterface
    {
        return $this->getContainer()->get('nia.core.entity.reference.factory');
    }

    protected function createStatusEnum(int $status): StatusEnum
    {
        return $this->getEnumFactory()->create(StatusEnum::class, $status);
    }

    public function loadYamlFixture(string $yamlFilePath): array
    {
        return Yaml::parseFile($yamlFilePath);
    }

    public function createLocale(string $code, string $name): Locale
    {
        return new Locale($code, $name);
    }

    protected function tearDown()
    {
        if ($this->_resetToken) {
            $tokenStorage = $this->getContainer()->get('security.token_storage');
            $tokenStorage->setToken(null);
        }
        parent::tearDown();
    }

    /**
     * This method set up an unit test token with passed roles
     * After every teardown the container will forget this settings!
     *
     * @param array $roles
     */
    public function setUpTestTokenUntilTearDown(array $roles, User $user = null): void
    {
        $this->getContainer()->get('security.token_storage')->setToken(new UnitTestToken($roles, $user));
        $this->_resetToken = true;
    }

    public function createContextWithEmptyUser(?array $roles = [], ?AbstractUser $user = null, ?Locale $locale = null, ?string $env = 'test'): Context
    {
        if (empty($locale)) {
            $locale = $this->createLocale('hu', 'Magyar');
        }

        $contextFactory = $this->getContainer()->get('nia.core.security_context.factory');

        return $contextFactory->create(new ServiceToken($roles), $locale, $env, []);
    }

    public function createContext(?array $roles = [], ?AbstractUser $user = null, ?Locale $locale = null, ?string $env = 'test')
    {
        if (empty($user)) {
            $user = $this->createEmptyUser(42);
        }

        if (empty($locale)) {
            $locale = $this->createLocale('hu', 'Magyar');
        }

        $contextFactory = $this->getContainer()->get('nia.core.security_context.factory');

        return $contextFactory->create(new UsernamePasswordToken($user, null, 'unittest', $roles), $locale, $env, []);
    }

    public function cloneAndProtectedPropertySet($object, string $propertyName, $value)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);

        return $object;
    }

    public function createEmptyUser(int $withid = null): User
    {
        $user = $this->getContainer()->get('nia.user.user.manager')->createNew();

        if (!empty($withid)) {
            $user = $this->cloneAndProtectedPropertySet($user, 'id', $withid);
        }

        return $user;
    }

    public function createEntityWithId(AbstractManager $manager, int $id): Entity
    {
        $entity = $manager->createNew();

        return $this->cloneAndProtectedPropertySet($entity, 'id', $id);
    }

    public function createContextFactoryMock(Context $returnContext): ContextFactory
    {
        $factory = $this->createMock(ContextFactory::class);
        $factory->expects($this->any())->method('create')->willReturn($returnContext);
        $factory->expects($this->any())->method('createServiceContext')->willReturn($returnContext);

        return $factory;
    }
}
