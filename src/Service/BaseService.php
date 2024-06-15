<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Service;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Promotion\PromotionEntity;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Content\ProductStream\ProductStreamEntity;
use Shopware\Core\Content\Rule\RuleEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\Salutation\SalutationEntity;
use Shopware\Core\System\Tax\TaxEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseService
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly AbstractSalesChannelContextFactory $salesChannelContextFactory,
    ) {
    }

    private function getContext(): Context
    {
        return Context::createDefaultContext();
    }

    public function getLanguages(): array
    {
        /** @var EntityRepository $languageRepository */
        $languageRepository = $this->container->get('language.repository');

        /** @var LanguageEntity $deLanguage */
        $deLanguage = $languageRepository->search(
            (new Criteria())->addAssociations(['locale'])->addFilter(new EqualsFilter('locale.code', 'de-DE')),
            $this->getContext()
        )->first();

        /** @var LanguageEntity $enLanguage */
        $enLanguage = $languageRepository->search(
            (new Criteria())->addAssociations(['locale'])->addFilter(new EqualsFilter('locale.code', 'en-GB')),
            $this->getContext()
        )->first();

        return [
            'de' => $deLanguage->getId(),
            'en' => $enLanguage->getId()
        ];
    }

    public function getDefaultSalesChannel(): SalesChannelEntity
    {
        /** @var EntityRepository $salesChannelRepository */
        $salesChannelRepository = $this->container->get('sales_channel.repository');

        /** @var SalesChannelEntity $salesChannel */
        $salesChannel = $salesChannelRepository->search(
            (new Criteria())->addAssociations(['domains'])->addFilter(new EqualsFilter('typeId', Defaults::SALES_CHANNEL_TYPE_STOREFRONT))->setLimit(1),
            $this->getContext()
        )->first();

        return $salesChannel;
    }

    public function getDefaultTax(): TaxEntity
    {
        /** @var EntityRepository $taxRepository */
        $taxRepository = $this->container->get('tax.repository');

        /** @var TaxEntity $tax */
        $tax = $taxRepository->search(
            (new Criteria())->addSorting(new FieldSorting('position', 'ASC'))->setLimit(1),
            $this->getContext()
        )->first();

        return $tax;
    }

    public function getDefaultRule(): RuleEntity
    {
        /** @var Connection $connection */
        $connection = $this->container->get('Doctrine\DBAL\Connection');

        /** @var EntityRepository $ruleRepository */
        $ruleRepository = $this->container->get('rule.repository');

        $query = '
            SELECT LOWER(HEX(rule_id)) AS id
            FROM rule_condition
            WHERE type = "alwaysValid"
        ';
        $id = $connection->fetchOne($query);

        /** @var RuleEntity $rule */
        $rule = $ruleRepository->search(
            (new Criteria([$id]))->setLimit(1),
            $this->getContext()
        )->first();

        return $rule;
    }

    public function getRandomMedia(): MediaEntity
    {
        /** @var EntityRepository $productMediaRepository */
        $productMediaRepository = $this->container->get('product_media.repository');

        /** @var ProductMediaEntity[] $covers */
        $covers = $productMediaRepository->search(
            (new Criteria())
                ->addAssociations(['media']),
            $this->getContext()
        )->getElements();

        $covers = array_values($covers);

        srand();
        $index = rand(0, count($covers) - 1);

        $productMedia = $covers[$index];

        return $productMedia->getMedia();
    }

    public function getRandomRule(): RuleEntity
    {
        /** @var EntityRepository $ruleRepository */
        $ruleRepository = $this->container->get('rule.repository');

        /** @var RuleEntity[] $rules */
        $rules = $ruleRepository->search(
            (new Criteria()),
            $this->getContext()
        )->getElements();

        $rules = array_values($rules);

        srand();
        $index = rand(0, count($rules) - 1);

        $rule = $rules[$index];

        return $rule;
    }

    public function getRandomProducts(int $quantity, SalesChannelContext $salesChannelContext): array
    {
        /** @var Connection $connection */
        $connection = $this->container->get('Doctrine\DBAL\Connection');

        /** @var SalesChannelRepository $salesChannelProductRepository */
        $salesChannelProductRepository = $this->container->get('sales_channel.product.repository');

        $query = '
            SELECT LOWER(HEX(id))
            FROM product
            WHERE (child_count IS NULL) OR (child_count = 0)
            ORDER BY RAND()
            LIMIT ' . $quantity . '
        ';
        $ids = $connection->fetchFirstColumn($query);

        /** @var SalesChannelProductEntity[] $products */
        $products = $salesChannelProductRepository->search(
            (new Criteria())
                ->addFilter(new EqualsAnyFilter('id', $ids))
                ->addAssociations(['cover.media', 'options.group', 'categories', 'properties.group', 'media']),
            $salesChannelContext
        )->getElements();

        return array_values($products);
    }

    public function getSalesChannelContext(?CustomerEntity $customer = null, ?string $salesChannelId = null): SalesChannelContext
    {
        /** @var EntityRepository $customerRepository */
        $customerRepository = $this->container->get('customer.repository');

        if (!$customer instanceof CustomerEntity) {
            /** @var CustomerEntity $customer */
            $customer = $customerRepository->search(
                (new Criteria())
                    ->addFilter(new EqualsFilter('email', 'max@mustermann.de')),
                $this->getContext()
            )->first();
        }

        if ($salesChannelId === null) {
            $salesChannelId = $this->getDefaultSalesChannel()->getId();
        }

        /** @var EntityRepository $salesChannelRepository */
        $salesChannelRepository = $this->container->get('sales_channel.repository');

        /** @var SalesChannelEntity $salesChannel */
        $salesChannel = $salesChannelRepository->search(
            (new Criteria())->addAssociations(['domains'])->addFilter(new EqualsFilter('id', $salesChannelId))->setLimit(1),
            $this->getContext()
        )->first();

        return $this->salesChannelContextFactory->create(
            Uuid::randomHex(),
            $salesChannelId,
            [
                SalesChannelContextService::CUSTOMER_ID => $customer->getId(),
                SalesChannelContextService::BILLING_ADDRESS_ID => $customer->getDefaultBillingAddressId(),
                SalesChannelContextService::SHIPPING_ADDRESS_ID => $customer->getDefaultShippingAddressId(),
                SalesChannelContextService::DOMAIN_ID => $salesChannel->getDomains()->first()->getId()
            ]
        );
    }

    public function createPromotion(string $name, string $code, string $type = 'percentage', float $value = 10.0): PromotionEntity
    {
        /** @var EntityRepository $promotionRepository */
        $promotionRepository = $this->container->get('promotion.repository');

        $id = Uuid::randomHex();

        $data = [
            'id' => $id,
            'active' => true,
            'maxRedemptionsGlobal' => 999,
            'maxRedemptionsPerCustomer' => 999,
            'code' => $code,
            'useCodes' => true,
            'customerRestriction' => false,
            'discounts' => [[
                'id' => Uuid::randomHex(),
                'promotionId' => $id,
                'scope' => 'cart',
                'type' => 'percentage',
                'value' => $value,
                'considerAdvancedRules' => false,
                'sorterKey' => 'PRICE_ASC',
                'applierKey' => 'ALL',
                'usageKey' => 'ALL',
                'discountRules' => []
            ]],
            'translations' => [
                Defaults::LANGUAGE_SYSTEM => [
                    'name' => $name
                ]
            ],
            'salesChannels' => [[
                'id' => Uuid::randomHex(),
                'priority' => 1,
                'promotionId' => $id,
                'salesChannelId' => $this->getDefaultSalesChannel()->getId()
            ]],
            'cartRules' => [],
            'personaRules' => [],
            'preventCombination' => true
        ];

        $promotionRepository->create(
            [$data],
            $this->getContext()
        );

        /** @var PromotionEntity $promotion */
        $promotion = $promotionRepository->search(
            new Criteria([$id]),
            $this->getContext()
        )->first();

        return $promotion;
    }

    public function createProductStreamAllProducts(): ProductStreamEntity
    {
        /** @var EntityRepository $productStreamRepository */
        $productStreamRepository = $this->container->get('product_stream.repository');

        $languages = $this->getLanguages();

        $id = Uuid::randomHex();
        $a = Uuid::randomHex();
        $b = Uuid::randomHex();
        $c = Uuid::randomHex();

        $productStreamRepository->create([[
            'id' => $id,
            'name' => [
                'de-DE' => 'Alle Produkte',
                'en-GB' => 'All products'
            ],
            'filters' => [
                [
                    'id' => $a,
                    'productStreamId' => $id,
                    'type' => 'multi',
                    'field' => null,
                    'operator' => 'OR',
                    'value' => null,
                    'position' => 0,
                ],
                [
                    'id' => $b,
                    'productStreamId' => $id,
                    'parentId' => $a,
                    'type' => 'multi',
                    'field' => null,
                    'operator' => 'AND',
                    'value' => null,
                    'position' => 0,
                ],
                [
                    'id' => $c,
                    'productStreamId' => $id,
                    'parentId' => $b,
                    'type' => 'equals',
                    'field' => 'active',
                    'operator' => null,
                    'value' => '1',
                    'position' => 0,
                ]
            ]
        ]], $this->getContext());

        /** @var ProductStreamEntity $productStream */
        $productStream = $productStreamRepository->search(
            (new Criteria([$id])),
            $this->getContext()
        )->first();

        return $productStream;
    }

    public function getLastCategory(): CategoryEntity
    {
        /** @var EntityRepository $categoryRepository */
        $categoryRepository = $this->container->get('category.repository');

        /** @var CategoryEntity $root */
        $root = $categoryRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('level', 1))
                ->addSorting(new FieldSorting('autoIncrement', 'ASC'))
                ->setLimit(1),
            $this->getContext()
        )->first();

        /** @var CategoryEntity $lastCategory */
        $lastCategory = $categoryRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('parentId', $root->getId()))
                ->addSorting(new FieldSorting('autoIncrement', 'DESC'))
                ->setLimit(1),
            $this->getContext()
        )->first();

        return $lastCategory;
    }

    public function createCategory(string $deName, string $enName, ?string $parentId = null, ?string $afterCategoryId = null): CategoryEntity
    {
        /** @var EntityRepository $categoryRepository */
        $categoryRepository = $this->container->get('category.repository');

        if ($parentId === null && $afterCategoryId === null) {
            $lastCategory = $this->getLastCategory();

            $parentId = $lastCategory->getParentId();
            $afterCategoryId = $lastCategory->getId();
        }

        $category = [
            'id' => Uuid::randomHex(),
            'parentId' => $parentId,
            'afterCategoryId' => $afterCategoryId,
            'active' => true,
            'visible' => true,
            'type' => 'page',
            'name' => [
                'de-DE' => $deName,
                'en-GB' => $enName
            ]
        ];

        $categoryRepository->create([$category], $this->getContext());

        /** @var CategoryEntity $category */
        $category = $categoryRepository->search(
            (new Criteria([$category['id']])),
            $this->getContext()
        )->first();

        return $category;
    }

    public function createProduct(string $deName, string $enName, string $number, float $netPrice, float $grossPrice, CategoryEntity $category, ?ProductMediaEntity $productMedia = null, ?SalesChannelEntity $salesChannel = null, ?TaxEntity $tax = null, ?int $visibility = null): ProductEntity
    {
        /** @var EntityRepository $productMediaRepository */
        $productMediaRepository = $this->container->get('product_media.repository');

        /** @var EntityRepository $productRepository */
        $productRepository = $this->container->get('product.repository');

        $languages = $this->getLanguages();

        if ($productMedia === null) {
            /** @var ProductMediaEntity[] $covers */
            $covers = $productMediaRepository->search(
                (new Criteria())
                    ->addAssociations(['media']),
                $this->getContext()
            )->getElements();

            $covers = array_values($covers);

            srand();
            $index = rand(0, count($covers) - 1);

            $productMedia = $covers[$index];
        }

        if ($salesChannel === null) {
            $salesChannel = $this->getDefaultSalesChannel();
        }

        if ($tax === null) {
            $tax = $this->getDefaultTax();
        }

        if ($visibility === null) {
            $visibility = 30;
        }

        $id = Uuid::randomHex();

        $product = [
            'id' => $id,
            'taxId' => $tax->getId(),
            'coverId' => $productMedia->getId(),
            'price' => [[
                'currencyId' => Defaults::CURRENCY,
                'net' => $netPrice,
                'linked' => true,
                'gross' => $grossPrice
            ]],
            'productNumber' => $number,
            'active' => true,
            'translations' => [
                [
                    'languageId' => $languages['de'],
                    'name' => $deName,
                    'description' => "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.   \n<br /><br />\nDuis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.   \n<br /><br />\nUt wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.   \n<br /><br />\nDuis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis.",
                ],
                [
                    'languageId' => $languages['en'],
                    'name' => $enName,
                    'description' => "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.   \n<br /><br />\nDuis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.   \n<br /><br />\nUt wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.   \n<br /><br />\nDuis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis.",
                ]
            ],
            'media' => [[
                'id' => Uuid::randomHex(),
                'mediaId' => $productMedia->getMediaId(),
                'position' => 0
            ]],
            'visibilities' => [[
                'id' => Uuid::randomHex(),
                'productId' => $id,
                'salesChannelId' => $salesChannel->getId(),
                'visibility' => $visibility
            ]],
            'categories' => [[
                'id' => $category->getId()
            ]],
            'stock' => 100
        ];

        $productRepository->create(
            [$product],
            $this->getContext()
        );

        /** @var ProductEntity $product */
        $product = $productRepository->search(
            (new Criteria([$product['id']])),
            $this->getContext()
        )->first();

        return $product;
    }

    public function createHomeElementProducts(array $productIds, int $position): void
    {
        /** @var EntityRepository $demoshopHomeElementRepository */
        $demoshopHomeElementRepository = $this->container->get('dvsn_demoshop_foundation_home_element.repository');

        $languages = $this->getLanguages();

        $element = [
            'id' => Uuid::randomHex(),
            'position' => $position,
            'type' => 'products',
            'payload' => ['ids' => $productIds],
            'translations' => [
                [
                    'languageId' => $languages['de'],
                    'translatablePayload' => []
                ],
                [
                    'languageId' => $languages['en'],
                    'translatablePayload' => []
                ]
            ],
        ];

        $demoshopHomeElementRepository->create(
            [$element],
            $this->getContext()
        );
    }

    public function createHomeElementAlert(string $deContent, string $enContent, int $position, string $type = 'info'): void
    {
        /** @var EntityRepository $demoshopHomeElementRepository */
        $demoshopHomeElementRepository = $this->container->get('dvsn_demoshop_foundation_home_element.repository');

        $languages = $this->getLanguages();

        $element = [
            'id' => Uuid::randomHex(),
            'position' => $position,
            'type' => 'alert',
            'payload' => ['type' => $type],
            'translations' => [
                [
                    'languageId' => $languages['de'],
                    'translatablePayload' => ['content' => $deContent]
                ],
                [
                    'languageId' => $languages['en'],
                    'translatablePayload' => ['content' => $enContent]
                ]
            ]
        ];

        $demoshopHomeElementRepository->create(
            [$element],
            $this->getContext()
        );
    }

    public function createHomeElementContent(string $deContent, string $enContent, int $position): void
    {
        /** @var EntityRepository $demoshopHomeElementRepository */
        $demoshopHomeElementRepository = $this->container->get('dvsn_demoshop_foundation_home_element.repository');

        $languages = $this->getLanguages();

        $element = [
            'id' => Uuid::randomHex(),
            'position' => $position,
            'type' => 'content',
            'payload' => [],
            'translations' => [
                [
                    'languageId' => $languages['de'],
                    'translatablePayload' => ['content' => $deContent]
                ],
                [
                    'languageId' => $languages['en'],
                    'translatablePayload' => ['content' => $enContent]
                ]
            ]
        ];

        $demoshopHomeElementRepository->create(
            [$element],
            $this->getContext()
        );
    }

    public function parseSql(string $query)
    {
        /** @var Connection $connection */
        $connection = $this->container->get('Doctrine\DBAL\Connection');

        $languages = $this->getLanguages();

        $query = str_replace(
            [':language-de', ':language-en'],
            ['0x' . $languages['de'], '0x' . $languages['en']],
            $query
        );

        $str = '
            SELECT LOWER(HEX(id)) AS id, product_number
            FROM product
            ORDER BY parent_id DESC, product_number DESC
        ';
        $products = $connection->fetchAllAssociative($str);

        foreach ($products as $product) {
            $query = str_replace(
                ':product-' . $product['product_number'],
                '0x' . $product['id'],
                $query
            );
        }

        return $query;
    }

    public function parseArray(array $arr): array
    {
        /** @var Connection $connection */
        $connection = $this->container->get('Doctrine\DBAL\Connection');

        $languages = $this->getLanguages();

        $replace = [
            'language-de' => $languages['de'],
            'language-en' => $languages['en'],
        ];

        $str = '
            SELECT LOWER(HEX(id)) AS id, product_number
            FROM product
            ORDER BY parent_id DESC, product_number DESC
        ';
        $products = $connection->fetchAllAssociative($str);

        foreach ($products as $product) {
            $replace['product-' . $product['product_number']] = $product['id'];
        }

        $arr = $this->parseArrayRecursive($arr, $replace);

        return $arr;
    }

    private function parseArrayRecursive(array $arr, array $replace): array
    {
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $arr[$key] = $this->parseArrayRecursive($value, $replace);
                continue;
            }

            if (!is_string($value)) {
                continue;
            }

            $arr[$key] = $this->parseArrayReplaceValue($value, $replace);
        }

        return $arr;
    }

    private function parseArrayReplaceValue(string $value, array $replace): string
    {
        foreach ($replace as $k => $v) {
            $value = str_replace($k, $v, $value);
        }

        return $value;
    }

    public function createCustomer(?SalesChannelEntity $salesChannel = null): CustomerEntity
    {
        $firstNames = [
            'mr' => ['Wolfgang', 'Michael', 'Werner', 'Klaus', 'Thomas', 'Jürgen', 'Andreas', 'Dieter', 'Frank', 'Bernd', 'Uwe'],
            'mrs' => ['Maria', 'Monika', 'Petra', 'Helga', 'Brigitte', 'Andrea', 'Claudia', 'Anna', 'Heike', 'Ulrike', 'Kerstin']
        ];

        $lastNames = ['Müller', 'Schmidt', 'Schneider', 'Fischer', 'Weber', 'Meyer', 'Wagner', 'Becker', 'Schulz', 'Hoffmann'];

        $cities = ['Berlin', 'Hamburg', 'München', 'Köln', 'Frankfurt', 'Stuttgart', 'Düsseldorf', 'Leipzig', 'Dortmund', 'Essen', 'Bremen', 'Hannover'];

        $streets = ['Hauptstraße', 'Schulstraße', 'Gartenstraße', 'Dorfstraße', 'Bahnhofstraße', 'Bergstraße', 'Birkenweg', 'Lindenstraße', 'Kirchstraße', 'Waldstraße'];

        $salutationKeys = ['mr', 'mrs'];

        $emailProviders = ['gmx.de', 'gmail.com', 'mail.de', 'web.de', 'telekom.de', 'freenet.de', 'yahoo.com', 'outlook.com'];

        /** @var EntityRepository $customerRepository */
        $customerRepository = $this->container->get('customer.repository');

        /** @var EntityRepository $customerGroupRepository */
        $customerGroupRepository = $this->container->get('customer_group.repository');

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

        $salesChannelId = null;
        $boundSalesChannelId = null;

        if ($salesChannel === null) {
            $salesChannel = $this->getDefaultSalesChannel();
            $salesChannelId = $salesChannel->getId();
        } else {
            $salesChannelId = $salesChannel->getId();
            $boundSalesChannelId = $salesChannel->getId();
        }

        /** @var SalutationEntity $salutation */
        $salutation = $salutationRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('salutationKey', $salutationKeys[array_rand($salutationKeys)]))->setLimit(1),
            Context::createDefaultContext()
        )->first();

        /** @var CountryEntity $country */
        $country = $countryRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('iso', 'DE'))->setLimit(1),
            Context::createDefaultContext()
        )->first();

        $firstName = $firstNames[$salutation->getSalutationKey()][array_rand($firstNames[$salutation->getSalutationKey()])];
        $lastName = $lastNames[array_rand($lastNames)];
        $city = $cities[array_rand($cities)];
        $street = $streets[array_rand($streets)];
        $emailProvider = $emailProviders[array_rand($emailProviders)];

        $customer = [
            'id' => Uuid::randomHex(),
            'customerNumber' => (string) rand(10000, 20000),
            'salesChannelId' => $salesChannelId,
            'boundSalesChannelId' => $boundSalesChannelId,
            'languageId' => $languages['de'],
            'groupId' => $customerGroup->getId(),
            'requestedGroupId' => null,
            'defaultPaymentMethodId' => $salesChannel->getPaymentMethodId(),
            'salutationId' => $salutation->getId(),
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'ou', 'ue', 'ss'], strtolower($firstName) . '.' . strtolower($lastName) . '-' . substr(md5(microtime()), 0, 8) . '@' . $emailProvider),
            'password' => md5(microtime()),
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
            'firstName' => $firstName,
            'lastName' => $lastName,
            'street' => $street . ' ' . (string) rand(5, 150),
            'zipcode' => (string) rand(10000, 95000),
            'city' => $city,
            'countryId' => $country->getId(),
            'countryStateId' => null
        ];

        $address['customerId'] = $customer['id'];

        $customer['defaultShippingAddressId'] = $address['id'];
        $customer['defaultBillingAddressId'] = $address['id'];
        $customer['addresses'][] = $address;

        $customerRepository->create(
            [$customer],
            Context::createDefaultContext()
        );

        /** @var CustomerEntity $entity */
        $entity = $customerRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('id', $customer['id'])),
            $this->getContext()
        )->first();

        return $entity;
    }

    public function createSalesChannel(string $name, string $url): SalesChannelEntity
    {
        $context = $this->getContext();
        $defaultSalesChannel = $this->getDefaultSalesChannel();
        $languages = $this->getLanguages();

        /** @var Connection $connection */
        $connection = $this->container->get('Doctrine\DBAL\Connection');

        /** @var EntityRepository $countryRepository */
        $countryRepository = $this->container->get('country.repository');

        /** @var EntityRepository $salesChannelRepository */
        $salesChannelRepository = $this->container->get('sales_channel.repository');

        /** @var EntityRepository $shippingMethodRepository */
        $shippingMethodRepository = $this->container->get('shipping_method.repository');

        /** @var EntityRepository $paymentMethodRepository */
        $paymentMethodRepository = $this->container->get('payment_method.repository');

        /** @var \Shopware\Core\System\Country\CountryEntity $countyDE */
        $countyDE = $countryRepository->search((new Criteria())->addFilter(new EqualsFilter('iso', 'DE')), $context)->first();

        /** @var \Shopware\Core\Checkout\Payment\PaymentMethodEntity[] $paymentMethods */
        $paymentMethods = array_values($paymentMethodRepository->search((new Criteria())->addFilter(new EqualsFilter('active', true))->addSorting(new FieldSorting('position', 'ASC')), $context)->getElements());

        /** @var \Shopware\Core\Checkout\Shipping\ShippingMethodEntity[] $shippingMethods */
        $shippingMethods = array_values($shippingMethodRepository->search((new Criteria())->addFilter(new EqualsFilter('active', true))->addSorting(new FieldSorting('position', 'ASC')), $context)->getElements());

        $paymentMethodIds = [];
        $shippingMethodIds = [];

        foreach ($paymentMethods as $paymentMethod) {
            $paymentMethodIds[] = ['id' => $paymentMethod->getId()];
        }

        foreach ($shippingMethods as $shippingMethod) {
            $shippingMethodIds[] = ['id' => $shippingMethod->getId()];
        }

        $salesChannelArr = [
            'id' => Uuid::randomHex(),
            'accessKey' => strtoupper(md5(microtime())),
            'active' => true,
            'countries' => [['id' => $countyDE->getId()]],
            'countryId' => $countyDE->getId(),
            'currencies' => [['id' => Defaults::CURRENCY]],
            'currencyId' => Defaults::CURRENCY,
            'customerGroupId' => $defaultSalesChannel->getCustomerGroupId(),
            'domains' => [[
                'id' => Uuid::randomHex(),
                'currencyId' => Defaults::CURRENCY,
                'hreflangUseOnlyLocale' => false,
                'languageId' => $languages['de'],
                'snippetSetId' => $defaultSalesChannel->getDomains()->first()->getSnippetSetId(),
                'url' => $url
            ]],
            'languageId' => $languages['de'],
            'languages' => [['id' => $languages['de']]],
            'name' => $name,
            'navigationCategoryId' => $defaultSalesChannel->getNavigationCategoryId(),
            'paymentMethodId' => $paymentMethods[0]->getId(),
            'paymentMethods' => $paymentMethodIds,
            'shippingMethodId' => $shippingMethods[0]->getId(),
            'shippingMethods' => $shippingMethodIds,
            'taxCalculationType' => $defaultSalesChannel->getTaxCalculationType(),
            'typeId' => $defaultSalesChannel->getTypeId()
        ];

        $salesChannelRepository->create([$salesChannelArr], $context);

        /** @var SalesChannelEntity $salesChannel */
        $salesChannel = $salesChannelRepository->search(new Criteria([$salesChannelArr['id']]), $context)->first();

        $query = '
            INSERT INTO product_visibility
                SELECT
                    UNHEX(MD5(HEX(id))) AS id,
                    product_id,
                    product_version_id,
                    0x' . $salesChannel->getId() . ' AS sales_channel_id,
                    visibility,
                    NOW() AS created_at,
                    NULL AS updated_at
                FROM product_visibility
                WHERE sales_channel_id = 0x' . $defaultSalesChannel->getId() . '
        ';
        $connection->executeStatement($query);

        return $salesChannel;
    }
}
