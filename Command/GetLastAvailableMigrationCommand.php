<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Command;

use Nia\CoreBundle\Entity\Factory\EntityFactoryInterface;
use Nia\CoreBundle\Entity\Manager\EntityManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class GetLastAvailableMigrationCommand extends AbstractCommand
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

    public function setEntityFactory(EntityFactoryInterface $entityFactory): void
    {
        $this->entityFactory = $entityFactory;
    }

    protected function configure()
    {
        $this
            ->setName('nia:migrations:getlastavailable')
            ->setDescription('Get the name of the last migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
            ++$i;
            $runnablePatches[$fileName] = $fileName;
        }

        krsort($runnablePatches);
        $lastPatch = current($runnablePatches);
        $output->writeln($lastPatch);
    }
}
