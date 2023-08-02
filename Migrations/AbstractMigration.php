<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Migrations;

use Nia\CoreBundle\Entity\Manager\EntityManager;

abstract class AbstractMigration
{
    /**
     * @var EntityManager
     */
    private $em;

    private $outputMessage = '';

    public function __construct(Entitymanager $em)
    {
        $this->em = $em;
    }

    protected function getDoctrine()
    {
        return $this->getDoctrine();
    }

    protected function fetchAll(string $sql)
    {
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    protected function fetch(string $sql)
    {
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getOutputMessage(): string
    {
        return $this->outputMessage;
    }

    protected function execute(string $sql): void
    {
        $this->outputMessage .= sprintf("%s\n", $sql);
        $stmt = $this->em->getConnection()->prepare($sql);
        if ($stmt->execute()) {
            $this->outputMessage .= "OK!\n";
        } else {
            $this->outputMessage .= "ERROR!\n";
        }
        $this->outputMessage .= '-----------------------------'."\n";
    }

    abstract public function migrate(): void;

    abstract public function rollback(): void;
}
