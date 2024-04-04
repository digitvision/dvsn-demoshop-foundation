<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Setup;

use Exception;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetEntity;

class Uninstall
{
    use Helper\TranslationTrait;
    use Helper\SalesChannelTrait;

    public function __construct(
        protected readonly UninstallContext $context,
        protected readonly Connection $connection,
        protected readonly EntityRepository $customFieldSetRepository
    ) {
    }

    public function uninstall(): void
    {
        throw new \Exception('uninstall method not allowed');

        if ($this->context->keepUserData()) {
            return;
        }

        $this->removeDbTables();
    }

    private function removeDbTables(): void
    {
        $drop = implode(" \n ", array_map(
            function($table) {return 'DROP TABLE IF EXISTS `' . $table . '`;';},
            DataHolder\DbTables::$tables
        ));

        $query = '
            SET FOREIGN_KEY_CHECKS=0;
            ' . $drop . '
            SET FOREIGN_KEY_CHECKS=1;
        ';
        $this->connection->executeStatement($query);

        foreach (DataHolder\DbTables::$columns as $column) {
            $split = explode('.', $column);

            $query = 'ALTER TABLE `' . $split[0] . '` DROP COLUMN `' . $split[1] . '`;';
            try {
                $this->connection->executeStatement($query);
            }
            catch (Exception $exception) {}
        }
    }
}
