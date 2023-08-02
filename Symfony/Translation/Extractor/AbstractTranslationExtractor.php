<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Symfony\Translation\Extractor;

use Nia\CoreBundle\Symfony\Translation\Extractor\Traits\PrefixFixerTrait;
use Nia\CoreBundle\Utils\BundleNameHelper;
use Symfony\Component\Translation\Extractor\PhpExtractor;

abstract class AbstractTranslationExtractor extends PhpExtractor
{
    use PrefixFixerTrait;

    protected $bundleNameHelper;

    public function __construct()
    {
        $this->bundleNameHelper = new BundleNameHelper();
    }

    protected function getKeyPrefix(string $directory): string
    {
        if ('' === $this->bundleNameHelper->getBundleNameByDirectory($directory)) {
            return 'AppBundle@';
        }

        return str_replace('Nia\\', 'Nia', $this->bundleNameHelper->getBundleNameByDirectory($directory)).'@';
    }
}
