<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nia\CoreBundle\Entity\Traits\EntityTrait;
use Nia\CoreBundle\Entity\Traits\IdentifiableEntityTrait;
use Nia\CoreBundle\Enum\LogEventEnum;

/**
 * Log.
 *
 * @ORM\Table(name="log",indexes={
 *     @ORM\Index(name="user_idx", columns={"user_id","created_at"}),
 *     @ORM\Index(name="event_idx", columns={"event","created_at"}),
 * })
 * @ORM\Entity(repositoryClass="Nia\CoreBundle\Repository\LogRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Log implements Entity, IdentifiableEntityInterface
{
    use EntityTrait;
    use IdentifiableEntityTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    protected $user_id;

    /**
     * @var LogEventEnum
     *
     * @ORM\Column(name="event", type="logEventEnum")
     */
    protected $event;

    /**
     * @var string
     *
     * @ORM\Column(name="target", type="string", length=255)
     */
    protected $target;

    /**
     * @var string
     *
     * @ORM\Column(name="info", type="text", nullable=true)
     */
    protected $info;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    protected $url;

    /**
     * @ORM\Column(type="datetime", nullable=false, options={"default": "CURRENT_TIMESTAMP"} )
     *
     * @var \DateTime
     */
    protected $created_at;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=16)
     */
    protected $ip = '';

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId(?int $user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @param string $target
     */
    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    /**
     * @return LogEventEnum
     */
    public function getEvent(): LogEventEnum
    {
        return $this->event;
    }

    /**
     * @param LogEventEnum $event
     */
    public function setEvent(LogEventEnum $event): void
    {
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getInfo(): ?string
    {
        return $this->info;
    }

    /**
     * @param string $info
     */
    public function setInfo(?string $info): void
    {
        $this->info = $info;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    /**
     * @param \DateTime $created_at
     */
    public function setCreatedAt(\DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        if (mb_strlen($url) > 255) {
            $url = mb_substr($url, 0, 255);
        }
        $this->url = $url;
    }
}
