<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Symfony\Translation\Extractor;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;

class EnumExtractor extends AbstractTranslationExtractor
{
    public function extract($directory, MessageCatalogue $catalog)
    {
        $keyPrefix = $this->getKeyPrefix($directory);

        if ('AppBundle@' === $keyPrefix) {
            $directory = realpath($directory.'/../src/Enum');
        } else {
            $directory = realpath($directory.'/../../Enum');
        }

        if ((false === $directory) || (!file_exists($directory))) {
            return;
        }

        $finder = new Finder();
        $finder->files()->in($directory);

        $bundle = $this->bundleNameHelper->getBundleNameByDirectory($directory);

        foreach ($finder->files() as $file) {
            if ('Enum.php' === mb_substr($file->getFileName(), -8)) {
                $className = $this->bundleNameHelper->getClassNameFromFileName($file->getFileName());
                $classNameWithNameSpace = $this->bundleNameHelper->getNamespacedClassNameFromPath($bundle, $directory, $file->getRealPath());

                foreach ($classNameWithNameSpace::keys() as $key) {
                    $transKey = $keyPrefix.'enum.'.$className.'.'.$key;
                    $value = $catalog->get($transKey) ?? $transKey;
                    if ($value === $transKey) {
                        $value = '__'.$value;
                    }
                    $catalog->set($transKey, $value, 'messages');
                }
            }
        }

        $this->removeTranslationsWithWrongPrefix($keyPrefix, $catalog);
    }
}
