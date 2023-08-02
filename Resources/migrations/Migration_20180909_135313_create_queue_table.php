<?php

declare(strict_types=1);
use Nia\CoreBundle\Migrations\AbstractMigration;

class Migration_20180909_135313_create_queue_table extends AbstractMigration
{
    public function migrate(): void
    {
        $this->execute(
            'CREATE TABLE queue (id VARCHAR(20) NOT NULL, processable_start DATETIME NOT NULL, locked DATETIME DEFAULT NULL, fail_count DATETIME DEFAULT NULL, success DATETIME DEFAULT NULL, message LONGTEXT NOT NULL, error LONGTEXT DEFAULT NULL, priority SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;'
        );

        $this->execute('ALTER TABLE queue CHANGE fail_count fail_count INT DEFAULT NULL;');
    }

    public function rollback(): void
    {
        // This patch file created before rollback function
    }
}
