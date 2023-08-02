<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Command;

use Esemve\Collection\StringCollection;
use Nia\CoreBundle\Entity\Factory\EntityFactoryInterface;
use Nia\CoreBundle\Entity\Manager\EntityManager;
use Nia\CoreBundle\Entity\Migration;
use Nia\CoreBundle\Migrations\AbstractMigration;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class MigrateCommand extends AbstractCommand
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @var EntityFactoryInterface
     */
    protected $entityFactory;

    /**
     * @var string
     */
    protected $appType;

    /**
     * @var string
     */
    protected $databaseUrl;

    public function setEntityManager(EntityManager $em): void
    {
        $this->em = $em;
    }

    public function setProjectDir(string $projectDir): void
    {
        $this->projectDir = $projectDir;
    }

    public function setEntityFactory(EntityFactoryInterface $entityFactory): void
    {
        $this->entityFactory = $entityFactory;
    }

    public function setAppType(string $appType): void
    {
        $this->appType = $appType;
    }

    public function setDatabaseUrl(string $databaseUrl): void
    {
        $this->databaseUrl = $databaseUrl;
    }

    protected function configure()
    {
        $this
            ->setName('nia:migrations:migrate')
            ->setDescription('Execute all not runned migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setOutputFormat($output);

        if ('test' === $this->appType || 'e2e' === $this->appType) {
            $this->dropTestDatabase($this->databaseUrl);
        }

        // db -k letrehozasa ha kellene...
        $this->createDatabase($this->databaseUrl);

        $patches = $this->getExecutedPatches();

        $finder = new Finder();
        if (!file_exists($this->projectDir.'/src/Migrations')) {
            mkdir($this->projectDir.'/src/Migrations');
        }
        if (file_exists($this->projectDir.'/vendor/nia/')) {
            $finder->in([$this->projectDir.'/src/Migrations/', $this->projectDir.'/vendor/nia/*/Resources/migrations/'])->sortByName();
        } elseif (file_exists($this->projectDir.'/nia/')) {
            $finder->in([$this->projectDir.'/src/Migrations/', $this->projectDir.'/nia/*/Resources/migrations/'])->sortByName();
        }

        $i = 0;

        $runnablePatches = [];

        /*
         * @var SplFileInfo
         */
        foreach ($finder->files() as $file) {
            $fileName = str_replace('.php', '', $file->getFilename());
            if ('.gitkeep' === $file->getFilename()) {
                continue;
            }
            if (false === $patches->search($fileName)) {
                ++$i;
                $runnablePatches[$fileName] = $file;
            }
        }

        ksort($runnablePatches);

        foreach ($runnablePatches as $fileName => $file) {
            $this->runPatch($file, $fileName, $output);
        }

        if ('test' === $this->appType) {
            $this->truncateTestDatabase($this->databaseUrl);
        }

        if (0 === $i) {
            $output->writeln('Your database is up to date!');
        }
    }

    protected function generateBundleName(?string $bundle = null): ?string
    {
        if (null === $bundle) {
            return null;
        }

        $bundle = mb_strtolower($bundle);
        $bundle = str_replace(['nia', '-'], '', $bundle);
        $bundle = str_replace('bundle', '-bundle', $bundle);

        return $bundle;
    }

    protected function getExecutedPatches(): StringCollection
    {
        try {
            $patches = $this->em->getRepository(Migration::class)->findAll();

            $output = [];
            foreach ($patches as $patch) {
                $output[] = $patch->getMigration();
            }
        } catch (\Exception $e) {
            $output = [];
        }

        return new StringCollection($output);
    }

    private function runPatch(SplFileInfo $file, string $fileName, OutputInterface $output): void
    {
        require_once $file->getRealPath();

        /**
         * @var AbstractMigration
         */
        $class = new $fileName($this->em);
        $class->migrate();

        $output->writeln(
            '<white>Run '.$file->getRealPath().' migration...</white>'.
            "\n".
            $this->colorize($class->getOutputMessage())
        );

        /**
         * @var Migration
         */
        $patchLog = $this->entityFactory->create(Migration::class);
        $patchLog->setMigration($fileName);

        $this->em->persist($patchLog);
        $this->em->flush();
    }

    private function createDatabase(string $url): void
    {
        $parsed = parse_url($url);
        $dbname = str_replace('/', '', $parsed['path']);

        $pdo = new \PDO(
            'mysql:host='.$parsed['host'].':'.$parsed['port'],
            $parsed['user'],
            $parsed['pass'],
            [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]
        );
        try {
            $pdo->exec('CREATE DATABASE IF NOT EXISTS '.$dbname);
        } catch (\PDOException $ex) {
        }
        unset($pdo);
    }

    private function dropTestDatabase(string $url): void
    {
        $parsed = parse_url($url);
        $dbname = str_replace('/', '', $parsed['path']);

        $pdo = new \PDO(
            'mysql:host='.$parsed['host'].':'.$parsed['port'],
            $parsed['user'],
            $parsed['pass'],
            [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]
        );
        try {
            $pdo->exec('DROP DATABASE '.$dbname);
        } catch (\PDOException $ex) {
        }
        unset($pdo);
    }

    private function truncateTestDatabase(string $url): void
    {
        $parsed = parse_url($url);
        $dbname = str_replace('/', '', $parsed['path']);

        $pdo = new \PDO(
            'mysql:host='.$parsed['host'].':'.$parsed['port'],
            $parsed['user'],
            $parsed['pass'],
            [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]
        );

        try {
            $statement = $pdo->prepare("SELECT `table_name` as name FROM information_schema.TABLES where table_schema = '".$dbname."';");
            $statement->execute();
            $tables = $statement->fetchAll();

            foreach ($tables as $table) {
                $pdo->exec('TRUNCATE TABLE '.$dbname.'.'.$table['name']);
            }
        } catch (\PDOException $ex) {
        }
        unset($pdo);
    }

    private function setOutputFormat(OutputInterface $output): void
    {
        $white = new OutputFormatterStyle('white', null);
        $output->getFormatter()->setStyle('white', $white);

        $green = new OutputFormatterStyle('black', 'green');
        $output->getFormatter()->setStyle('greenok', $green);

        $green = new OutputFormatterStyle('green', null);
        $output->getFormatter()->setStyle('green', $green);

        $red = new OutputFormatterStyle('black', 'red');
        $output->getFormatter()->setStyle('rederror', $red);

        $red = new OutputFormatterStyle('yellow', null);
        $output->getFormatter()->setStyle('yellow', $red);

        $magenta = new OutputFormatterStyle('magenta', null);
        $output->getFormatter()->setStyle('magenta', $magenta);

        $cyan = new OutputFormatterStyle('cyan', null);
        $output->getFormatter()->setStyle('cyan', $cyan);
    }

    private function colorize(string $text): string
    {
        // Ez itt ocsmány, de egyelőre így marad...
        // Ha valakinek lesz kedve akkor ebből lehetne valami szebbet varázsolni, de
        // addig is működik...
        $text = str_replace('OK!', '<greenok>OK!</greenok>', $text);
        $text = str_replace('ERROR!', '<rederror>ERROR!</rederror>', $text);
        $text = str_replace('CREATE TABLE', '<yellow>CREATE TABLE</yellow>', $text);
        $text = str_replace('CREATE INDEX', '<yellow>CREATE TABLE</yellow>', $text);
        $text = str_replace('INSERT INTO', '<green>INSERT INTO</green>', $text);
        $text = str_replace(' ON ', ' <yellow>ON</yellow> ', $text);
        $text = str_replace(',', '<yellow>,</yellow>', $text);
        $text = str_replace('(', '<yellow>(</yellow>', $text);
        $text = str_replace(')', '<yellow>)</yellow>', $text);
        $text = str_replace('NULL', '<yellow>NULL</yellow>', $text);
        $text = str_replace('NOT', '<yellow>NOT</yellow>', $text);
        $text = str_replace('ADD', '<yellow>ADD</yellow>', $text);
        $text = str_replace('TINYINT', '<yellow>TINYINT</yellow>', $text);
        $text = str_replace('TEXT', '<yellow>TEXT</yellow>', $text);
        $text = str_replace('DEFAULT', '<yellow>DEFAULT</yellow>', $text);
        $text = str_replace('VARCHAR', '<yellow>VARCHAR</yellow>', $text);
        $text = str_replace('DATETIME', '<yellow>DATETIME</yellow>', $text);
        $text = str_replace(' DATE ', ' <yellow>DATE</yellow> ', $text);
        $text = str_replace('PRIMARY', '<yellow>PRIMARY</yellow>', $text);
        $text = str_replace(' INT ', ' <yellow>INT</yellow> ', $text);
        $text = str_replace(' SMALLINT ', ' <yellow>SMALLINT</yellow> ', $text);
        $text = str_replace(' KEY', ' <yellow>KEY</yellow>', $text);
        $text = str_replace(' SET ', ' <yellow>SET</yellow> ', $text);
        $text = str_replace('ALTER TABLE', '<cyan>ALTER TABLE</cyan>', $text);
        $text = str_replace(' CHANGE ', ' <cyan>CHANGE</cyan> ', $text);
        $text = str_replace(' COLUMN ', ' <cyan>COLUMN</cyan> ', $text);
        $text = str_replace('DROP INDEX', '<magenta>DROP TABLE</magenta>', $text);
        $text = str_replace('DROP', '<magenta>DROP</magenta>', $text);

        return $text;
    }
}
