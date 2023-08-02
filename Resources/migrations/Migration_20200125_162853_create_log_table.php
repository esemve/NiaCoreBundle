<?php

declare(strict_types=1);

use Nia\CoreBundle\Migrations\AbstractMigration;

class Migration_20200125_162853_create_log_table extends AbstractMigration
{
    public function migrate(): void
    {
        $this->execute('  CREATE TABLE log (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, event INT NOT NULL COMMENT \'(DC2Type:logEventEnum)\', target VARCHAR(255) NOT NULL, info LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ip VARCHAR(16) NOT NULL, INDEX user_idx (user_id, created_at), INDEX event_idx (event, created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC;');
    }

    public function rollback(): void
    {
        $this->execute('DROP TABLE IF EXISTS log;');
    }
}
