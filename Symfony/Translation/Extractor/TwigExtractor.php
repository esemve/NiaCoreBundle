<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Symfony\Translation\Extractor;

use Nia\CoreBundle\Symfony\Translation\Extractor\Traits\PrefixFixerTrait;
use Nia\CoreBundle\Utils\BundleNameHelper;
use Symfony\Bridge\Twig\Translation\TwigExtractor as BaseTwigExtractor;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
use Twig\Environment;

class TwigExtractor extends BaseTwigExtractor
{
    use PrefixFixerTrait;

    protected $bundleNameHelper;

    public function __construct(Environment $twig)
    {
        $this->bundleNameHelper = new BundleNameHelper();
        parent::__construct($twig);
    }

    public function extract($directory, MessageCatalogue $catalog)
    {
        if (is_file($directory)) {
            return;
        }
        $finder = new Finder();

        if ('AppBundle@' === $this->getKeyPrefix($directory)) {
            $viewFolders = $finder->files()->name('*.twig')->in($directory);
        } else {
            $viewFolders = $finder->directories()->depth('>1')->in($directory.'/../../../')->name('*view*');
        }
        foreach ($viewFolders as $viewFolder) {
            try {
                parent::extract($viewFolder->getPath(), $catalog);
            } catch (\Exception $ex) {
            }
        }

        $this->removeTranslationsWithWrongPrefix($this->getKeyPrefix($directory), $catalog);
    }

    protected function getKeyPrefix(string $directory): string
    {
        if ('' === $this->bundleNameHelper->getBundleNameByDirectory($directory)) {
            return 'AppBundle@';
        }

        return str_replace('Nia\\', 'Nia', $this->bundleNameHelper->getBundleNameByDirectory($directory)).'@';
    }
}
