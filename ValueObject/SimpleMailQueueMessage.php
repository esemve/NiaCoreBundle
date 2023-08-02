<?php

declare(strict_types=1);

namespace Nia\CoreBundle\ValueObject;

class SimpleMailQueueMessage extends AbstractQueueMessage
{
    /**
     * @var string
     */
    protected $html;
    /**
     * @var string
     */
    protected $txt;
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $subject;

    public function __construct(string $subject, string $email, string $html, string $txt)
    {
        $this->txt = $txt;
        $this->html = $html;
        $this->email = $email;
        $this->subject = $subject;
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    public function getTxt(): string
    {
        return $this->txt;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }
}
