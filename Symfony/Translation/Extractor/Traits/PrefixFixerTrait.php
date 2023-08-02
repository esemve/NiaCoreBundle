<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Symfony\Translation\Extractor\Traits;

use Symfony\Component\Translation\MessageCatalogue;

trait PrefixFixerTrait
{
    protected function removeTranslationsWithWrongPrefix(string $keyPrefix, MessageCatalogue $catalog): void
    {
        foreach ($catalog->all() as $domain => $translations) {
            $messages = [];
            if (!empty($translations)) {
                foreach ($translations as $key => $value) {
                    if ((mb_substr($key, 0, mb_strlen($keyPrefix)) === $keyPrefix) || ('ROLE@' === mb_substr($key, 0, 5)) || ('LOCALE@' === mb_substr($key, 0, 7))) {
                        $messages[$key] = $value;
                    }
                }
            }
            $catalog->replace($messages, $domain);
        }
    }
}
