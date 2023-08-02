<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Symfony\Translation\Extractor;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigExtractor extends AbstractTranslationExtractor
{
    public function extract($directory, MessageCatalogue $catalog)
    {
        $directory = realpath($directory.'/../config/');
        if ((false === $directory) || (!file_exists($directory))) {
            return;
        }

        $finder = new Finder();
        $this->parseConfig($finder->files()->in($directory)->depth(0)->name('config.yaml'), $catalog);

        $finder = new Finder();
        $this->parseConfig($finder->files()->in($directory)->depth('>0')->name('config.yaml'), $catalog);

        $this->removeTranslationsWithWrongPrefix($this->getKeyPrefix($directory), $catalog);
    }

    protected function parseConfig(Finder $finder, MessageCatalogueInterface $catalog): void
    {
        foreach ($finder->files() as $file) {
            $yaml = Yaml::parseFile($file->getRealPath());
            if (!\is_array($yaml)) {
                return;
            }

            $this->parseRoles($yaml, $catalog);
            $this->parseTranslationKeys($yaml, $catalog);
        }
    }

    protected function parseRoles(array $yaml, MessageCatalogueInterface $catalog): void
    {
        if (isset($yaml['nia_core']['available_roles'])) {
            foreach ((array) $yaml['nia_core']['available_roles'] as $role) {
                $transKey = 'ROLE@name.'.$role;

                $value = $catalog->get($transKey) ?? $transKey;
                if ($value === $transKey) {
                    $value = '__'.$value;
                }

                $catalog->set($transKey, $value, 'messages');
            }
        }
    }

    protected function parseTranslationKeys(array $yaml, MessageCatalogueInterface $catalog): void
    {
        if (isset($yaml['nia_core']['translation_keys'])) {
            foreach ((array) $yaml['nia_core']['translation_keys'] as $transKey) {
                $value = $catalog->get($transKey) ?? $transKey;
                if ($value === $transKey) {
                    $value = '__'.$value;
                }

                $catalog->set($transKey, $value, 'messages');
            }
        }
    }
}
