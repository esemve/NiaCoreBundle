<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Symfony\Translation\Extractor;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;

class TranslationsExtractor extends AbstractTranslationExtractor
{
    const EXCEPT_DIRS = [
        'Test',
        'Tests',
        'Enum',
    ];

    public function extract($directory, MessageCatalogue $catalog)
    {
        $keyPrefix = $this->getKeyPrefix($directory);

        $finder = new Finder();

        if ('AppBundle@' === $keyPrefix) {
            if (!file_exists($directory.'/../src/')) {
                return;
            }
            $finder->directories()->depth(1)->in($directory.'/../src/');
        } else {
            if (is_file($directory)) {
                return;
            }
            $finder->directories()->depth(1)->in($directory.'/../../../');
        }

        foreach ($finder as $file) {
            if (!\in_array($file->getFilename(), self::EXCEPT_DIRS, true)) {
                if ($file->isDir()) {
                    $this->bundleExtract($file->getRealPath(), $catalog);
                }
            }
        }

        $this->removeTranslationsWithWrongPrefix($keyPrefix, $catalog);
    }

    private function bundleExtract(string $directory, MessageCatalogue $catalog)
    {
        if (is_file($directory)) {
            return;
        }
        parent::extract($directory, $catalog);
    }
}
