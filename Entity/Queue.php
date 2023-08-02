<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nia\CoreBundle\ValueObject\AbstractQueueMessage;
use Nia\CoreBundle\ValueObject\QueueMessageInterface;

/**
 * Queue.
 *
 * @ORM\Table(name="queue")
 * @ORM\Entity(repositoryClass="Nia\CoreBundle\Repository\QueueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Queue
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string", length=20)
     * @ORM\Id
     */
    protected $id;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="processable_start", type="datetime")
     */
    protected $processable_start;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="locked", type="datetime", nullable=true)
     */
    protected $locked;

    /**
     * @var int
     *
     * @ORM\Column(name="fail_count", type="integer", nullable=true)
     */
    protected $fail_count;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="success", type="datetime", nullable=true)
     */
    protected $success;

    /**
     * @var QueueMessageInterface
     *
     * @ORM\Column(name="message", type="text")
     */
    protected $message;

    /**
     * @var string
     *
     * @ORM\Column(name="error", type="text", nullable=true)
     */
    protected $error;

    /**
     * @var int
     *
     * @ORM\Column(name="priority", type="smallint", nullable=false)
     */
    protected $priority = AbstractQueueMessage::PRIORITY_5;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getProcessableStart(): \DateTimeInterface
    {
        return $this->processable_start;
    }

    public function setProcessableStart(\DateTimeInterface $processable_start): self
    {
        $this->processable_start = $processable_start;

        return $this;
    }

    public function getLocked(): ?\DateTimeInterface
    {
        return $this->locked;
    }

    public function setLocked(?\DateTimeInterface $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    public function getFailCount(): int
    {
        return $this->fail_count ? $this->fail_count : 0;
    }

    public function setFailCount(int $fail_count): self
    {
        $this->fail_count = $fail_count;

        return $this;
    }

    public function getSuccess(): \DateTimeInterface
    {
        return $this->success;
    }

    public function setSuccess(\DateTimeInterface $success): self
    {
        $this->success = $success;

        return $this;
    }

    public function getMessage(): QueueMessageInterface
    {
        return unserialize($this->message);
    }

    public function setMessage(QueueMessageInterface $message): self
    {
        $this->message = serialize($message);

        return $this;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function setError(string $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }
}
