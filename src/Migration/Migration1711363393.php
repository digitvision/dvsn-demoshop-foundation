<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1711363393 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1711363393;
    }

    public function update(Connection $connection): void
    {
        $query = '
            CREATE TABLE IF NOT EXISTS `dvsn_demoshop_foundation_home_element` (
                `id` BINARY(16) NOT NULL,
                `position` INT(11) NOT NULL,
                `type` VARCHAR(255) NOT NULL,
                `payload` JSON NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3),
                PRIMARY KEY (`id`)
            )
            ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
                    
            CREATE TABLE IF NOT EXISTS `dvsn_demoshop_foundation_home_element_translation` (
                `dvsn_demoshop_foundation_home_element_id` BINARY(16) NOT NULL,
                `language_id` BINARY(16) NOT NULL,
                `translatable_payload` JSON NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3),
                PRIMARY KEY (`dvsn_demoshop_foundation_home_element_id`, `language_id`),
                CONSTRAINT `fk.dvsn_dhet.dvsn_demoshop_foundation_home_element_id` FOREIGN KEY (`dvsn_demoshop_foundation_home_element_id`)
                    REFERENCES `dvsn_demoshop_foundation_home_element` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.dvsn_dhet.language_id` FOREIGN KEY (`language_id`)
                    REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
            ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
        ';
        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
