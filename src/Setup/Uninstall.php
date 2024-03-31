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
        if ($this->context->keepUserData()) {
            return;
        }

        $this->removeCustomFields();
        $this->removeDbTables();
        $this->removeNumberRanges();
        $this->removeDocuments();
    }

    private function removeCustomFields(): void
    {
        foreach (DataHolder\CustomFields::$customFields as $customFieldSet) {
            foreach ($customFieldSet['customFields'] as $customField) {
                foreach ($customFieldSet['relations'] as $relation) {
                    $query = '
                        UPDATE `' . $relation['entityName'] . '`
                        SET `custom_fields` = JSON_REMOVE(`custom_fields`, "$.' . $customField['name'] . '");
                    ';
                    try {
                        $this->connection->executeStatement($query);
                    } catch (Exception $exception) {}

                    $query = '
                        UPDATE `' . $relation['entityName'] . '_translation`
                        SET `custom_fields` = JSON_REMOVE(`custom_fields`, "$.' . $customField['name'] . '");
                    ';
                    try {
                        $this->connection->executeStatement($query);
                    } catch (Exception $exception) {}
                }
            }
        }

        foreach (DataHolder\CustomFields::$customFields as $customField) {
            $customFieldSet = $this->customFieldSetRepository->search(
                (new Criteria())
                    ->addFilter(new EqualsFilter('custom_field_set.name', $customField['name'])),
                $this->context->getContext()
            )->first();

            if (!$customFieldSet instanceof CustomFieldSetEntity) {
                continue;
            }

            $this->customFieldSetRepository->delete(
                [['id' => $customFieldSet->getId()]],
                $this->context->getContext()
            );
        }
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

    private function removeNumberRanges(): void
    {
        foreach (DataHolder\NumberRanges::$numberRanges as $numberRange) {
            $query = '
                SET FOREIGN_KEY_CHECKS=0;
                DELETE FROM number_range_translation WHERE number_range_id = :id;
                DELETE FROM number_range_state WHERE number_range_id = :id;
                DELETE FROM number_range_sales_channel WHERE number_range_id = :id;
                DELETE FROM number_range WHERE id = :id;
                SET FOREIGN_KEY_CHECKS=1;
            ';
            $this->connection->executeStatement($query, [
                'id' => Uuid::fromHexToBytes($numberRange['id'])
            ]);
        }

        foreach (DataHolder\NumberRanges::$numberRanges as $numberRange) {
            $query = '
                SET FOREIGN_KEY_CHECKS=0;
                DELETE FROM number_range_type_translation WHERE number_range_type_id = :id;
                DELETE FROM number_range_type WHERE id = :id;
                SET FOREIGN_KEY_CHECKS=1;
            ';
            $this->connection->executeStatement($query, [
                'id' => Uuid::fromHexToBytes($numberRange['type']['id'])
            ]);
        }
    }

    private function removeDocuments(): void
    {
        foreach (DataHolder\Documents::$documentTypes as $documentType) {
            $query = '
                SET FOREIGN_KEY_CHECKS=0;
                DELETE FROM document_base_config_sales_channel WHERE document_type_id = :id;
                SET FOREIGN_KEY_CHECKS=1;
            ';
            $this->connection->executeStatement($query, [
                'id' => Uuid::fromHexToBytes($documentType['id'])
            ]);
        }

        foreach (DataHolder\Documents::$documentTypes as $documentType) {
            $query = '
                SET FOREIGN_KEY_CHECKS=0;
                DELETE FROM document_type_translation WHERE document_type_id = :id;
                DELETE FROM document_type WHERE id = :id;
                SET FOREIGN_KEY_CHECKS=1;
            ';
            $this->connection->executeStatement($query, [
                'id' => Uuid::fromHexToBytes($documentType['id'])
            ]);
        }

        foreach (DataHolder\Documents::$documentBaseConfigs as $documentBaseConfig) {
            $query = '
                SET FOREIGN_KEY_CHECKS=0;
                DELETE FROM document_base_config WHERE id = :id;
                SET FOREIGN_KEY_CHECKS=1;
            ';
            $this->connection->executeStatement($query, [
                'id' => Uuid::fromHexToBytes($documentBaseConfig['id'])
            ]);
        }
    }
}
