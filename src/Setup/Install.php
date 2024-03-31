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
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Uuid\Uuid;

class Install
{
    use Helper\TranslationTrait;
    use Helper\SalesChannelTrait;

    public function __construct(
        protected readonly InstallContext $context,
        protected readonly Connection $connection,
        protected readonly EntityRepository $customFieldSetRepository,
        protected readonly EntityRepository $numberRangeRepository,
        protected readonly EntityRepository $mailTemplateRepository,
        protected readonly EntityRepository $documentTypeRepository,
        protected readonly EntityRepository $documentBaseConfigRepository,
        protected readonly EntityRepository $promitionRepository
    ) {
    }

    public function install(): void
    {
        $this->installCustomFields();
        $this->installNumberRanges();
        $this->installEmailTemplates();
        $this->installDocuments();
        $this->installPromotions();
    }

    private function installCustomFields(): void
    {
        foreach (DataHolder\CustomFields::$customFields as $customField) {
            try {
                $this->customFieldSetRepository->upsert(
                    [$customField],
                    $this->context->getContext()
                );
            }
            catch (Exception $exception) {}
        }
    }

    private function installEmailTemplates(): void
    {
        foreach (DataHolder\MailTemplates::$mailTemplates as $mailTemplate) {
            $mailTemplate = $this->parseTranslations($mailTemplate);
            $mailTemplate['mailTemplateType'] = $this->parseTranslations($mailTemplate['mailTemplateType']);

            $this->mailTemplateRepository->upsert(
                [$mailTemplate],
                Context::createDefaultContext()
            );
        }
    }

    private function installNumberRanges(): void
    {
        foreach (DataHolder\NumberRanges::$numberRanges as $numberRange) {
            $numberRange = $this->parseTranslations($numberRange);
            $numberRange['type'] = $this->parseTranslations($numberRange['type']);

            $this->numberRangeRepository->upsert(
                [$numberRange],
                Context::createDefaultContext()
            );
        }
    }

    private function installDocuments(): void
    {
        foreach (DataHolder\Documents::$documentTypes as $documentType) {
            $documentType = $this->parseTranslations($documentType);

            $this->documentTypeRepository->upsert(
                [$documentType],
                Context::createDefaultContext()
            );
        }

        foreach (DataHolder\Documents::$documentBaseConfigs as $documentBaseConfig) {
            $this->documentBaseConfigRepository->upsert(
                [$documentBaseConfig],
                Context::createDefaultContext()
            );
        }

        foreach (DataHolder\Documents::$documentBaseConfigSalesChannels as $documentbaseConfigSalesChannel) {
            try {
                $this->connection->insert(
                    'document_base_config_sales_channel',
                    [
                        'id' => Uuid::fromHexToBytes($documentbaseConfigSalesChannel['id']),
                        'document_base_config_id' => Uuid::fromHexToBytes($documentbaseConfigSalesChannel['documentBaseConfigId']),
                        'document_type_id' => Uuid::fromHexToBytes($documentbaseConfigSalesChannel['documentTypeId']),
                        'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)
                    ]
                );
            } catch (Exception $exception) {}
        }
    }

    private function installPromotions(): void
    {
        $salesChannels = $this->getSalesChannels();

        foreach (DataHolder\Promotions::$promotions as $promotion) {
            $promotion = $this->parseTranslations($promotion);

            foreach ($salesChannels as $salesChannel) {
                $promotion['salesChannels'][] = [
                    'id' => Uuid::randomHex(),
                    'priority' => 1,
                    'promotionId' => $promotion['id'],
                    'salesChannelId' => $salesChannel
                ];
            }

            try {
                $this->promitionRepository->upsert(
                    [$promotion],
                    Context::createDefaultContext()
                );
            }
            catch (Exception $exception) {}
        }
    }
}
