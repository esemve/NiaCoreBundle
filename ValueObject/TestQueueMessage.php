<?php

declare(strict_types=1);

namespace Nia\CoreBundle\ValueObject;

class TestQueueMessage extends AbstractQueueMessage
{
    /**
     * @var string
     */
    protected $text;

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }
}
