<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TranslationUpdateCommand extends AbstractCommand
{
    protected $locales;

    public function setLocales(array $locales): void
    {
        $this->locales = $locales;
    }

    protected function configure()
    {
        $this
            ->setName('nia:translation:update')
            ->addArgument('bundle', InputArgument::OPTIONAL, 'Bundle name', '')
            ->setDescription('Update translations for a bundle.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bundle = $input->getArgument('bundle');

        if ('nia' === mb_substr(mb_strtolower($bundle), 0, 3)) {
            $output->writeln('Wrong bundle name format!');
            $output->writeln('For example if you want generate for NiaMenuBundle, use only "MenuBundle" string for generate!');

            return;
        }

        $bundleFolder = $this->bundlePathGuesser($bundle);

        foreach ($this->locales as $locale) {
            $this->doGenerate($bundleFolder, $locale, $output);
        }

        $output->writeln('Done!');
    }

    protected function doGenerate($bundlePath, $locale, OutputInterface $output)
    {
        $command = $this->getApplication()->find('translation:update');
        $params = [
            'command' => $command->getName(),
            'locale' => $locale,
            '--output-format' => 'yml',
            '--force' => true,
            '--quiet' => true,
            '--clean' => true,
            '--no-backup' => true,
        ];

        if (!empty($bundlePath)) {
            $params['bundle'] = $bundlePath;
        }

        $input = new ArrayInput($params);

        $command->run($input, $output);
    }

    protected function bundlePathGuesser(string $bundle = ''): string
    {
        if (empty($bundle)) {
            return $this->nonNiaBundlePathGuesser();
        }

        if (false !== mb_strpos(__DIR__, 'vendor')) {
            die('You can run this command with parameter only in Nia Development repo!');
        }

        $bundle = mb_strtolower($bundle);
        $bundle = ucfirst(str_replace('bundle', 'Bundle', $bundle));

        $kernel = $this->getApplication()->getKernel();
        $rootDir = $kernel->getRootDir();

        $bundlePath = sprintf('%s/../nia/%s', $rootDir, $bundle);

        $views = $bundlePath.'/Resources/views';
        $translations = $bundlePath.'/Resources/translations';

        if (!file_exists($views)) {
            mkdir($views);
        }

        if (!file_exists($translations)) {
            mkdir($translations);
        }

        return sprintf('%s/../nia/%s', $rootDir, $bundle);
    }

    protected function nonNiaBundlePathGuesser(): string
    {
        return '';
    }
}
