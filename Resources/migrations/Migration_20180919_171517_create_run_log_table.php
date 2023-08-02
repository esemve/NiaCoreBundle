<?php

declare(strict_types=1);

use Nia\CoreBundle\Migrations\AbstractMigration;

class Migration_20180919_171517_create_run_log_table extends AbstractMigration
{
    public function migrate(): void
    {
        $this->execute(' CREATE TABLE run_log (`log_key` VARCHAR(20) NOT NULL, `last_time` DATETIME NOT NULL, PRIMARY KEY(`log_key`)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;');
    }

    public function rollback(): void
    {
        // This patch file created before rollback function
    }
}
