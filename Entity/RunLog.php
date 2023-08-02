<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RunLog.
 *
 * @ORM\Table(name="run_log")
 * @ORM\Entity(repositoryClass="Nia\CoreBundle\Repository\RunLogRepository")
 * @ORM\HasLifecycleCallbacks
 */
class RunLog
{
    /**
     * @var string
     *
     * @ORM\Column(name="log_key", type="string", length=20)
     * @ORM\Id
     */
    protected $key;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="last_time", type="datetime")
     */
    protected $time;

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getTime(): \DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
    }
}
