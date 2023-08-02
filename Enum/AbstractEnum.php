<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Enum;

use MyCLabs\Enum\Enum;

class AbstractEnum extends Enum
{
    public function getTranslation()
    {
        $exploded = explode('\\', \get_called_class());
        if ('Nia' === $exploded[0]) {
            return $exploded[0].$exploded[1].'@enum.'.$exploded[\count($exploded) - 1].'.'.$this->getKey();
        }
        /*
         * @todo talán... de azért majd érdemes megnézni, hogy működik-e...
         */
        return 'App@enum.'.$exploded[\count($exploded) - 1].'.'.$this->getKey();
    }
}
