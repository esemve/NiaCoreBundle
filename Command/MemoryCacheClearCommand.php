<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Command;

use Nia\CoreBundle\Driver\AbstractCacheDriver;
use Nia\CoreBundle\Provider\CacheProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MemoryCacheClearCommand extends AbstractCommand
{
    /**
     * @var AbstractCacheDriver
     */
    protected $driver;

    public function setCacheProvider(CacheProvider $cacheProvider): void
    {
        $this->driver = $cacheProvider->provide();
    }

    protected function configure()
    {
        $this
            ->setName('nia:memory:cc')
            ->setDescription('Cleaning up redis / memcached cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Clearing...');
        $this->driver->clearByFilter('*');
        $output->writeln('Done!');
    }
}
