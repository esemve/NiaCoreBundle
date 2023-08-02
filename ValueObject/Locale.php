<?php

declare(strict_types=1);

namespace Nia\CoreBundle\ValueObject;

class Locale
{
    /**
     * @var string
     */
    private $code;
    /**
     * @var string
     */
    private $name;

    public function __construct(string $code, string $name)
    {
        $this->code = $code;
        $this->name = $name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->getCode();
    }
}
