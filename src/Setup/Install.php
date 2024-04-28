<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Setup;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupEntity;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Flow\FlowEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\Salutation\SalutationEntity;
use Shopware\Core\System\Snippet\Aggregate\SnippetSet\SnippetSetEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Install
{
    use Helper\TranslationTrait;
    use Helper\SalesChannelTrait;

    public function __construct(
        protected readonly InstallContext $context,
        protected readonly Connection $connection,
        protected readonly ContainerInterface $container
    ) {
    }

    public function install(): void
    {
        $this->installCategories();
        $this->installSalesChannel();
        $this->updateAdminUser();
        $this->installMaxMustermann();
    }

    private function installCategories(): void
    {
        $languages = $this->getLanguages();

        /** @var EntityRepository $categoryRepository */
        $categoryRepository = $this->container->get('category.repository');

        /** @var CategoryEntity $root */
        $root = $categoryRepository->search(
            (new Criteria())->addSorting(new FieldSorting('autoIncrement', FieldSorting::ASCENDING))->setLimit(1),
            $this->context->getContext()
        )->first();

        $catalogCategory = [
            'id' => Uuid::randomHex(),
            'afterCategoryId' => $root->getId(),
            'active' => true,
            'visible' => true,
            'type' => 'page',
            'translations' => [
                [
                    'languageId' => $languages['en'],
                    'name' => 'Catalogue #2'
                ],
                [
                    'languageId' => $languages['de'],
                    'name' => 'Katalog #2'
                ]
            ]
        ];

        $categoryRepository->upsert([$catalogCategory], $this->context->getContext());

        $parentCategory = [
            'id' => Uuid::randomHex(),
            'parentId' => $catalogCategory['id'],
            'active' => true,
            'visible' => true,
            'type' => 'folder',
            'translations' => [
                [
                    'languageId' => $languages['en'],
                    'name' => 'Legal information'
                ],
                [
                    'languageId' => $languages['de'],
                    'name' => 'Rechtliche Hinweise'
                ]
            ]
        ];

        $categoryRepository->upsert([$parentCategory], $this->context->getContext());

        $imprintCategory = [
            'id' => Uuid::randomHex(),
            'parentId' => $parentCategory['id'],
            'active' => true,
            'visible' => true,
            'type' => 'link',
            'translations' => [
                [
                    'languageId' => $languages['en'],
                    'name' => 'Imprint',
                    'linkType' => 'external',
                    'externalLink' => '/en/imprint'
                ],
                [
                    'languageId' => $languages['de'],
                    'name' => 'Impressum',
                    'linkType' => 'external',
                    'externalLink' => '/impressum'
                ]
            ]
        ];

        $privacyPolicyCategory = [
            'id' => Uuid::randomHex(),
            'parentId' => $parentCategory['id'],
            'afterCategoryId' => $imprintCategory['id'],
            'active' => true,
            'visible' => true,
            'type' => 'link',
            'translations' => [
                [
                    'languageId' => $languages['en'],
                    'name' => 'Privacy policy',
                    'linkType' => 'external',
                    'externalLink' => '/en/privacy-policy'
                ],
                [
                    'languageId' => $languages['de'],
                    'name' => 'Datenschutz',
                    'linkType' => 'external',
                    'externalLink' => '/datenschutz'
                ]
            ]
        ];

        $categoryRepository->upsert([$imprintCategory, $privacyPolicyCategory], $this->context->getContext());

        $this->connection->update(
            'sales_channel',
            ['footer_category_id' => Uuid::fromHexToBytes($catalogCategory['id'])],
            ['active' => 1, 'type_id' => Uuid::fromHexToBytes(Defaults::SALES_CHANNEL_TYPE_STOREFRONT)]
        );
    }

    private function installSalesChannel(): void
    {
        /** @var EntityRepository $salesChannelRepository */
        $salesChannelRepository = $this->container->get('sales_channel.repository');

        /** @var EntityRepository $salesChannelDomainRepository */
        $salesChannelDomainRepository = $this->container->get('sales_channel_domain.repository');

        $languages = $this->getLanguages();

        /** @var SalesChannelEntity $salesChannel */
        $salesChannel = $salesChannelRepository->search(
            (new Criteria())->addAssociations(['domains'])->addFilter(new EqualsFilter('typeId', Defaults::SALES_CHANNEL_TYPE_STOREFRONT)),
            $this->context->getContext()
        )->first();

        if ($salesChannel->getLanguageId() === $languages['de']) {
            $salesChannelDomain = $salesChannel->getDomains()->first();

            $domain = $salesChannelDomain->getUrl();
            $domain = str_replace(['https://', 'http://'], '', $domain);

            /** @var EntityRepository $snippetSetRepository */
            $snippetSetRepository = $this->container->get('snippet_set.repository');

            /** @var SnippetSetEntity $snippetSet */
            $snippetSet = $snippetSetRepository->search(
                (new Criteria())->addFilter(new EqualsFilter('iso', 'en-GB')),
                $this->context->getContext()
            )->first();

            $salesChannelDomainRepository->create([[
                'id' => Uuid::randomHex(),
                'salesChannelId' => $salesChannel->getId(),
                'languageId' => $languages['en'],
                'url' => 'https://' . $domain . '/en',
                'currencyId' => Defaults::CURRENCY,
                'snippetSetId' => $snippetSet->getId(),
                'hreflangUseOnlyLocale' => false
            ]], $this->context->getContext());

            /** @var EntityRepository $salesChannelLanguageRepository */
            $salesChannelLanguageRepository = $this->container->get('sales_channel_language.repository');

            $salesChannelLanguageRepository->upsert([[
                'salesChannelId' => $salesChannel->getId(),
                'languageId' => $languages['en']
            ]], $this->context->getContext());
        }

        if ($salesChannel->getLanguageId() === $languages['en']) {
            $salesChannelDomain = $salesChannel->getDomains()->first();

            $domain = $salesChannelDomain->getUrl();
            $domain = str_replace(['https://', 'http://'], '', $domain);

            $salesChannelDomainRepository->update([[
                'id' => $salesChannelDomain->getId(),
                'url' => 'https://' . $domain . '/en'
            ]], $this->context->getContext());

            /** @var EntityRepository $snippetSetRepository */
            $snippetSetRepository = $this->container->get('snippet_set.repository');

            /** @var SnippetSetEntity $snippetSet */
            $snippetSet = $snippetSetRepository->search(
                (new Criteria())->addFilter(new EqualsFilter('iso', 'de-DE')),
                $this->context->getContext()
            )->first();

            $salesChannelDomainRepository->create([[
                'id' => Uuid::randomHex(),
                'salesChannelId' => $salesChannel->getId(),
                'languageId' => $languages['de'],
                'url' => 'https://' . $domain,
                'currencyId' => Defaults::CURRENCY,
                'snippetSetId' => $snippetSet->getId(),
                'hreflangUseOnlyLocale' => false
            ]], $this->context->getContext());

            /** @var EntityRepository $salesChannelLanguageRepository */
            $salesChannelLanguageRepository = $this->container->get('sales_channel_language.repository');

            $salesChannelLanguageRepository->upsert([[
                'salesChannelId' => $salesChannel->getId(),
                'languageId' => $languages['de']
            ]], $this->context->getContext());
        }
    }

    private function updateAdminUser(): void
    {
        $languages = $this->getLanguages();

        /** @var EntityRepository $languageRepository */
        $languageRepository = $this->container->get('language.repository');

        /** @var LanguageEntity $deLanguage */
        $deLanguage = $languageRepository->search(
            (new Criteria([$languages['de']])),
            Context::createDefaultContext()
        )->first();

        $query = '
            UPDATE `user`
            SET `locale_id` = :id
        ';
        $this->connection->executeStatement(
            $query,
            ['id' => Uuid::fromHexToBytes($deLanguage->getLocaleId())]
        );
    }

    public function getLanguages(): array
    {
        /** @var EntityRepository $languageRepository */
        $languageRepository = $this->container->get('language.repository');

        /** @var LanguageEntity $deLanguage */
        $deLanguage = $languageRepository->search(
            (new Criteria())->addAssociations(['locale'])->addFilter(new EqualsFilter('locale.code', 'de-DE')),
            Context::createDefaultContext()
        )->first();

        /** @var LanguageEntity $enLanguage */
        $enLanguage = $languageRepository->search(
            (new Criteria())->addAssociations(['locale'])->addFilter(new EqualsFilter('locale.code', 'en-GB')),
            Context::createDefaultContext()
        )->first();

        return [
            'de' => $deLanguage->getId(),
            'en' => $enLanguage->getId()
        ];
    }

    private function installMaxMustermann(): void
    {
        /** @var EntityRepository $customerRepository */
        $customerRepository = $this->container->get('customer.repository');

        /** @var EntityRepository $customerGroupRepository */
        $customerGroupRepository = $this->container->get('customer_group.repository');

        /** @var EntityRepository $salesChannelRepository */
        $salesChannelRepository = $this->container->get('sales_channel.repository');

        /** @var EntityRepository $salutationRepository */
        $salutationRepository = $this->container->get('salutation.repository');

        /** @var EntityRepository $salutationRepository */
        $countryRepository = $this->container->get('country.repository');

        $languages = $this->getLanguages();

        /** @var CustomerGroupEntity $customerGroup */
        $customerGroup = $customerGroupRepository->search(
            (new Criteria())->setLimit(1),
            Context::createDefaultContext()
        )->first();

        /** @var SalesChannelEntity $salesChannel */
        $salesChannel = $salesChannelRepository->search(
            (new Criteria())->addAssociations(['domains'])->addFilter(new EqualsFilter('typeId', Defaults::SALES_CHANNEL_TYPE_STOREFRONT))->setLimit(1),
            Context::createDefaultContext()
        )->first();

        /** @var SalutationEntity $salutation */
        $salutation = $salutationRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('salutationKey', 'mr'))->setLimit(1),
            Context::createDefaultContext()
        )->first();

        /** @var CountryEntity $country */
        $country = $countryRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('iso', 'DE'))->setLimit(1),
            Context::createDefaultContext()
        )->first();

        $customer = [
            'id' => Uuid::randomHex(),
            'customerNumber' => '9999',
            'salesChannelId' => $salesChannel->getId(),
            'boundSalesChannelId' => null,
            'languageId' => $languages['de'],
            'groupId' => $customerGroup->getId(),
            'requestedGroupId' => null,
            'defaultPaymentMethodId' => $salesChannel->getPaymentMethodId(),
            'salutationId' => $salutation->getId(),
            'firstName' => 'Max',
            'lastName' => 'Mustermann',
            'email' => 'max@mustermann.de',
            'password' => '$2y$10$9rjt2sVaF8iv8kXAdS1mZ.NAxSUHyUexE0xfi6p8/DJToLl1i7dF2',
            'title' => null,
            'affiliateCode' => null,
            'campaignCode' => null,
            'active' => true,
            'birthday' => null,
            'guest' => false,
            'accountType' => 'private',
            'firstLogin' => new \DateTimeImmutable(),
            'addresses' => [],
            'customFields' => []
        ];

        $address = [
            'id' => Uuid::randomHex(),
            'salutationId' => $salutation->getId(),
            'firstName' => 'Max',
            'lastName' => 'Mustermann',
            'street' => 'Willy-Brandt-Straße 1',
            'zipcode' => '10557',
            'city' => 'Berlin',
            'countryId' => $country->getId(),
            'countryStateId' => null
        ];

        $address['customerId'] = $customer['id'];

        $customer['defaultShippingAddressId'] = $address['id'];
        $customer['defaultBillingAddressId'] = $address['id'];
        $customer['addresses'][] = $address;

        try {
            $customerRepository->create(
                [$customer],
                Context::createDefaultContext()
            );
        } catch (\Exception $exception) {}
    }
}
