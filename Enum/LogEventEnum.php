<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Enum;

class LogEventEnum extends AbstractEnum
{
    const CREATE = 1;
    const EDIT = 2;
    const DELETE = 3;
    const SHOW = 4;
    const LOG_IN = 5;
    const SEND = 6;
    const START = 7;
    const STOP = 8;
    const INFO = 9;
    const UPLOAD = 10;
}
