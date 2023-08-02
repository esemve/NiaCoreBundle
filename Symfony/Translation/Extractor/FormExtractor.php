<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Symfony\Translation\Extractor;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;

class FormExtractor extends AbstractTranslationExtractor
{
    const ALLOWED_DIRS = [
        'Form',
    ];

    protected $sequences = [
        [
            '"label"',
            '=>',
            self::MESSAGE_TOKEN,
        ],
        [
            "'label'",
            '=>',
            self::MESSAGE_TOKEN,
        ],
        [
            "'label'",
            '=>',
            '$options',
            '[',
            "'label'",
            ']',
            '??',
            self::MESSAGE_TOKEN,
        ],
        [
            '"placeholder"',
            '=>',
            self::MESSAGE_TOKEN,
        ],
        [
            "'placeholder'",
            '=>',
            self::MESSAGE_TOKEN,
        ],
        [
            "'placeholder'",
            '=>',
            '$options',
            '[',
            "'placeholder'",
            ']',
            '??',
            self::MESSAGE_TOKEN,
        ],
    ];

    public function extract($directory, MessageCatalogue $catalog)
    {
        if (is_file($directory)) {
            return;
        }
        $keyPrefix = $this->getKeyPrefix($directory);

        $finder = new Finder();
        if ('AppBundle@' === $keyPrefix) {
            if (!file_exists($directory.'/../src/')) {
                return;
            }
            $finder->directories()->in($directory.'/../src/');
        } else {
            $finder->directories()->depth(1)->in($directory.'/../../../');
        }

        foreach ($finder as $file) {
            if (\in_array($file->getFilename(), self::ALLOWED_DIRS, true)) {
                $this->bundleExtract($file->getRealPath(), $catalog);
            }
        }

        $this->removeTranslationsWithWrongPrefix($keyPrefix, $catalog);
    }

    private function bundleExtract(string $directory, MessageCatalogue $catalog)
    {
        parent::extract($directory, $catalog);
    }
}
