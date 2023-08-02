<?php

declare(strict_types=1);
use Nia\CoreBundle\Migrations\AbstractMigration;

class Migration_20180304_180913_create_patches_table extends AbstractMigration
{
    public function migrate(): void
    {
        $this->execute(
            'CREATE TABLE `migrations` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                PRIMARY KEY (`id`)
             ) ENGINE=InnoDB AUTO_INCREMENT=1 
             DEFAULT CHARSET=utf8 
             COLLATE=utf8_unicode_ci'
        );
    }

    public function rollback(): void
    {
        // This patch file created before rollback function
    }
}
