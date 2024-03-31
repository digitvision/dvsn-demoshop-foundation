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
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Uuid\Uuid;

class Update
{
    use Helper\TranslationTrait;
    use Helper\SalesChannelTrait;

    public function __construct(
        protected readonly InstallContext $context,
        protected readonly Connection $connection,
        protected readonly EntityRepository $customFieldSetRepository,
        protected readonly EntityRepository $customFieldRepository
    ) {
    }

    public function install(): void
    {
        $this->update('0.0.0');
    }

    public function update($preUpdateVersion): void
    {
        if ($preUpdateVersion === '0.0.0') {
            return;
        }

        $this->installCustomFields();
    }

    private function installCustomFields(): void
    {
        foreach ($this->getCustomFields() as $customField) {
            try {
                $this->customFieldSetRepository->upsert(
                    [$customField],
                    $this->context->getContext()
                );
            }
            catch (Exception $exception) {}
        }

        foreach ($this->getCustomFields() as $set) {
            foreach ($set['customFields'] as $customField) {
                $customField['customFieldSetId'] = $set['id'];

                $this->customFieldRepository->upsert(
                    [$customField],
                    $this->context->getContext()
                );
            }
        }
    }

    private function getCustomFields(): array
    {
        $customFields = DataHolder\CustomFields::$customFields;

        foreach ($customFields as $i => $group) {
            if ($group['id'] === null) {
                try {
                    $query = '
                        SELECT id
                        FROM custom_field_set
                        WHERE name = :name
                    ';
                    $id = $this->connection->fetchOne($query, ['name' => $group['name']]);

                    if ($id !== false) {
                        $customFields[$i]['id'] = Uuid::fromBytesToHex($id);
                    }
                } catch (Exception $exception) {
                }
            }

            foreach ($group['customFields'] as $j => $customField) {
                if ($customField['id'] === null) {
                    try {
                        $query = '
                            SELECT id
                            FROM custom_field
                            WHERE name = :name
                        ';
                        $id = $this->connection->fetchOne($query, ['name' => $customField['name']]);

                        if ($id !== false) {
                            $customFields[$i]['customFields'][$j]['id'] = Uuid::fromBytesToHex($id);

                        }
                    } catch (Exception $exception) {
                    }
                }
            }
        }

        return $customFields;
    }
}
