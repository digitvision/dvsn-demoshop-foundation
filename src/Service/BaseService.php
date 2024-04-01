<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Service;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\ProductStream\ProductStreamEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\Tax\TaxEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseService
{
    public function __construct(
        protected readonly ContainerInterface $container
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

    function createProductStreamAllProducts(): ProductStreamEntity
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
            'translations' => [
                [
                    'languageId' => $languages['de'],
                    'name' => 'Alle Produkte'
                ],
                [
                    'languageId' => $languages['en'],
                    'name' => 'All products'
                ]
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

    function createCategory(string $deName, string $enName, ?string $parentId = null, ?string $afterCategoryId = null): CategoryEntity
    {
        /** @var EntityRepository $categoryRepository */
        $categoryRepository = $this->container->get('category.repository');

        $languages = $this->getLanguages();

        if ($parentId === null && $afterCategoryId === null) {
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

            $parentId = $root->getId();
            $afterCategoryId = $lastCategory->getId();
        }

        $category = [
            'id' => Uuid::randomHex(),
            'parentId' => $parentId,
            'afterCategoryId' => $afterCategoryId,
            'active' => true,
            'visible' => true,
            'type' => 'page',
            'translations' => [
                [
                    'languageId' => $languages['de'],
                    'name' => $deName
                ],
                [
                    'languageId' => $languages['en'],
                    'name' => $enName
                ]
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

    function createProduct(string $deName, string $enName, string $number, float $netPrice, float $grossPrice, CategoryEntity $category, ?ProductMediaEntity $productMedia = null, ?SalesChannelEntity $salesChannel = null, ?TaxEntity $tax = null): ProductEntity
    {
        /** @var EntityRepository $productMediaRepository */
        $productMediaRepository = $this->container->get('product_media.repository');

        /** @var EntityRepository $salesChannelRepository */
        $salesChannelRepository = $this->container->get('sales_channel.repository');

        /** @var EntityRepository $taxRepository */
        $taxRepository = $this->container->get('tax.repository');

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
            $random = array_rand($covers, 1);

            $productMedia = $covers[$random];
        }

        if ($salesChannel === null) {
            /** @var SalesChannelEntity $salesChannel */
            $salesChannel = $salesChannelRepository->search(
                (new Criteria())->addAssociations(['domains'])->addFilter(new EqualsFilter('typeId', Defaults::SALES_CHANNEL_TYPE_STOREFRONT))->setLimit(1),
                $this->getContext()
            )->first();
        }

        if ($tax === null) {
            /** @var TaxEntity $tax */
            $tax = $taxRepository->search(
                (new Criteria())->addSorting(new FieldSorting('position', 'ASC'))->setLimit(1),
                $this->getContext()
            )->first();
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
                'visibility' => 30
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
}