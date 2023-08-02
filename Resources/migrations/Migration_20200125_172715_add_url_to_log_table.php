<?php

declare(strict_types=1);

use Nia\CoreBundle\Migrations\AbstractMigration;

class Migration_20200125_172715_add_url_to_log_table extends AbstractMigration
{
    public function migrate(): void
    {
        $this->execute('ALTER TABLE log ADD url VARCHAR(255) DEFAULT NULL;');
    }

    public function rollback(): void
    {
        $this->execute('ALTER TABLE log DROP url;');
    }
}
