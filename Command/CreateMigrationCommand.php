<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Command;

use Nia\CoreBundle\Entity\Manager\EntityManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateMigrationCommand extends AbstractCommand
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $projectDir;

    public function setEntityManager(EntityManager $em): void
    {
        $this->em = $em;
    }

    public function setProjectDir(string $projectDir): void
    {
        $this->projectDir = $projectDir;
    }

    protected function configure()
    {
        $this
            ->setName('nia:migrations:create')
            ->addArgument('patchname', InputArgument::REQUIRED, 'Migration name')
            ->addArgument('bundle', InputArgument::OPTIONAL, 'Bundle name')
            ->setDescription('Create a migration file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bundle = $input->getArgument('bundle');
        $patchname = $input->getArgument('patchname');

        if (!empty($bundle)) {
            if ('nia' === mb_substr(mb_strtolower($bundle), 0, 3)) {
                $output->writeln('Wrong bundle name format!');
                $output->writeln('For example if you want generate for NiaMenuBundle, use only "MenuBundle" string for generate!');

                return;
            }
            $bundle = $this->generateBundleName($bundle);
        }

        if (null === $bundle) {
            $fileName = $this->generateMigrationForProject($patchname);
        } else {
            $fileName = $this->generateMigrationForBundle($patchname, $bundle);
        }

        $output->writeln(sprintf('New migration file created %s', $this->pathFixer($fileName)));
    }

    protected function pathFixer(string $path): string
    {
        return str_replace('//', '/', $path);
    }

    protected function generateBundleName(?string $bundle = null): ?string
    {
        if (null === $bundle) {
            return null;
        }

        if (false !== mb_strpos(__DIR__, 'vendor')) {
            return $this->generateBundleNameForDevelopmentVendorMode($bundle);
        }

        $bundle = mb_strtolower($bundle);
        $bundle = ucfirst(str_replace('bundle', 'Bundle', $bundle));

        return $bundle;
    }

    protected function generateBundleNameForVendorMode(?string $bundle = null): ?string
    {
        if (null === $bundle) {
            return null;
        }

        $bundle = mb_strtolower($bundle);
        $bundle = str_replace(['nia', '-'], '', $bundle);
        $bundle = str_replace('bundle', '-bundle', $bundle);

        return $bundle;
    }

    protected function generateMigrationForProject($patchname): string
    {
        $version = $this->getVersion($patchname);

        $skeleton = file_get_contents(__DIR__.'/../Resources/skeletons/migrations/migration.txt');
        $skeleton = str_replace('{version}', $version, $skeleton);
        $projectDir = $this->projectDir.'/src/Migrations/';
        if (!file_exists($projectDir)) {
            mkdir($projectDir);
        }
        file_put_contents($projectDir.'/'.$version.'.php', $skeleton);

        return $projectDir.'/'.$version.'.php';
    }

    protected function generateMigrationForBundle($patchname, $bundle): string
    {
        $version = $this->getVersion($patchname);

        $skeleton = file_get_contents(__DIR__.'/../Resources/skeletons/migrations/migration.txt');
        $skeleton = str_replace('{version}', $version, $skeleton);

        if (false !== mb_strpos(__DIR__, 'vendor')) {
            $resourcesDir = $this->projectDir.'/vendor/nia/'.$bundle.'/Resources/migrations/';
        } else {
            $resourcesDir = $this->projectDir.'/nia/'.$bundle.'/Resources/migrations/';
        }

        if (!file_exists($resourcesDir)) {
            mkdir($resourcesDir);
        }
        file_put_contents($resourcesDir.'/'.$version.'.php', $skeleton);

        return $resourcesDir.'/'.$version.'.php';
    }

    protected function getVersion(string $patchname): string
    {
        return sprintf('Migration_%s_%s', date('Ymd_His'), $patchname);
    }
}
