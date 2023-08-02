<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Command;

use Nia\CoreBundle\Collections\EntityCollection;
use Nia\CoreBundle\Entity\Manager\EntityManager;
use Nia\CoreBundle\Entity\Migration;
use Nia\CoreBundle\Manager\MigrationManager;
use Nia\CoreBundle\Migrations\AbstractMigration;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class RollbackMigrationCommand extends AbstractCommand
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
     * @var string
     */
    protected $env;

    /**
     * @var MigrationManager
     */
    protected $patchLogManager;

    public function setEntityManager(EntityManager $em): void
    {
        $this->em = $em;
    }

    public function setPatchLogManager(MigrationManager $patchLogManager): void
    {
        $this->patchLogManager = $patchLogManager;
    }

    public function setProjectDir(string $projectDir): void
    {
        $this->projectDir = $projectDir;
    }

    public function setEnv(string $env): void
    {
        $this->env = $env;
    }

    protected function configure()
    {
        $this
            ->setName('nia:migrations:rollbackto')
            ->addArgument('to', InputArgument::REQUIRED, 'rollback to this patch (this patch will still patched!)')
            ->setDescription('Rollback all migration to parameter migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $to = $input->getArgument('to');

        $this->setOutputFormat($output);

        if ('test' === $this->env) {
            $output->writeln('You can\'t use this command with test env!');
        }

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
            if ('.gitkeep' === $fileName) {
                continue;
            }
            ++$i;
            $runnablePatches[$fileName] = $file;
        }

        ksort($runnablePatches);

        $lastId = $this->getPatchId($to);
        if (empty($lastId)) {
            $output->writeln(sprintf('Migration %s not found in executed migrations table!', $to));

            return;
        }

        $rollbackable = $this->getRollbackFromId($lastId);

        foreach ($rollbackable as $rollback) {
            if (empty($runnablePatches[$rollback->getMigration()])) {
                $output->writeln(sprintf('I cant rollback because the %s migration file is missing!', $rollbackable->getMigration()));

                return;
            }
        }

        foreach ($rollbackable as $rollback) {
            ++$i;
            $file = $runnablePatches[$rollback->getMigration()];
            $this->runRollback($file, $rollback->getMigration(), $output);
        }

        if (0 === $i) {
            $output->writeln('Not found any migration for rollback!');
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

    private function runRollback(SplFileInfo $file, string $fileName, OutputInterface $output): void
    {
        require_once $file->getRealPath();

        /**
         * @var AbstractMigration
         */
        $class = new $fileName($this->em);
        $class->rollback();

        $output->writeln(
            '<white>Rollback '.$file->getRealPath().' patch...</white>'.
            "\n".
            $this->colorize($class->getOutputMessage())
        );

        /**
         * @var Migration
         */
        $entity = $this->patchLogManager->findOneBy($this->patchLogManager->createCriteriaCollection([
            ['migration', '=', $fileName],
        ]));

        $this->patchLogManager->remove($entity, $this->getContext(), true);
    }

    protected function getPatchId(string $patch): ?int
    {
        try {
            $patch = $this->em->getRepository(Migration::class)->findOneBy(['migration' => $patch], ['id' => 'DESC']);

            if (!empty($patch)) {
                return $patch->getId();
            }
        } catch (\Exception $e) {
        }

        return null;
    }

    protected function getRollbackFromId(int $id): EntityCollection
    {
        try {
            return $this->patchLogManager->findAllBy($this->patchLogManager->createCriteriaCollection([
                ['id', '>', $id],
            ]), 'id', $this->patchLogManager->orderByDesc());
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }

        return null;
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
