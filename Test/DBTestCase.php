<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Test;

use Nia\CoreBundle\Collections\CriteriaCollection;
use Nia\CoreBundle\Collections\EntityCollection;
use Nia\CoreBundle\Entity\Manager\EntityManager;
use Nia\CoreBundle\Provider\CacheProvider;
use Nia\CoreBundle\Security\Factory\ContextFactory;
use Nia\CoreBundle\Security\QueryFilters\UnitTestQueryFilter;
use Nia\UserBundle\Manager\UserManager;
use PHPUnit\DbUnit\Database\Connection;
use PHPUnit\DbUnit\Database\DefaultConnection;
use PHPUnit\DbUnit\TestCaseTrait;

abstract class DBTestCase extends TestCase
{
    use TestCaseTrait {
        TestCaseTrait::setUp as dbSetUp;
        TestCaseTrait::tearDown as dbTearDown;
    }

    /**
     * @var DefaultConnection
     */
    protected static $conn;

    /**
     * @var \PDO
     */
    protected static $pdo;

    protected function getUnitTestQueryFilter(): UnitTestQueryFilter
    {
        return $this->getContainer()->get('nia.core.query_filter.unittest');
    }

    /**
     * Returns the test database connection.
     *
     * @return Connection
     */
    protected function getConnection()
    {
        if (null === self::$conn) {
            if (null === self::$pdo) {
                $url = $this->getContainer()->getParameter('database_url');

                if (('/var/www/symfony' !== mb_substr(__DIR__, 0, 16))) {
                    $url = str_replace('@db:3306', '@127.0.0.1:3307', $url);
                    $_ENV['DATABASE_URL'] = $url;
                    $_SERVER['DATABASE_URL'] = $url;
                }

                $parsed = parse_url($url);
                $dbname = str_replace('/', '', $parsed['path']);

                self::$pdo = new \PDO(
                    'mysql:host='.$parsed['host'].':'.$parsed['port'],
                    $parsed['user'],
                    $parsed['pass'],
                    [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]
                );
                self::$pdo->exec('CREATE DATABASE IF NOT EXISTS '.$dbname);
                self::$pdo->exec('USE '.$dbname);
            }

            self::$conn = $this->createDefaultDBConnection(self::$pdo, $dbname);
        }

        return self::$conn;
    }

    protected function createCriteriaCollection(array $array): CriteriaCollection
    {
        return new CriteriaCollection($array);
    }

    protected function createEntityCollection(array $array): EntityCollection
    {
        return new EntityCollection($array);
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->getContainer()->get('nia.core.entity.manager');
    }

    protected function getContextFactory(): ContextFactory
    {
        return $this->getContainer()->get('nia.core.security_context.factory');
    }

    protected function getUserManager(): UserManager
    {
        return $this->getContainer()->get('nia.user.user.manager');
    }

    protected function getCacheProvider(): CacheProvider
    {
        return $this->getContainer()->get('nia.core.cache.provider');
    }

    protected function tearDown()
    {
        $this->getCacheProvider()->provide()->clearByFilter('*');
        $this->getEntityManager()->getUnitOfWork()->clear();
        parent::tearDown();
    }
}
