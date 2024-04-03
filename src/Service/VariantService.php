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
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;

class VariantService
{
    public function __construct(
        protected readonly ContainerInterface $container,
        protected readonly MediaService $mediaService,
        protected string $pluginPath
    ) {
    }

    private function getContext(): Context
    {
        return Context::createDefaultContext();
    }

    public function updateTshirt(): void
    {
        /** @var EntityRepository $propertyGroupRepository */
        $propertyGroupRepository = $this->container->get('property_group.repository');

        /** @var EntityRepository $propertyOptionRepository */
        $propertyOptionRepository = $this->container->get('property_group_option.repository');

        /** @var EntityRepository $productRepository */
        $productRepository = $this->container->get('product.repository');

        /** @var Connection $connection */
        $connection = $this->container->get('Doctrine\DBAL\Connection');

        /** @var ProductEntity $parent */
        $parent = $productRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('productNumber', 'SWDEMO10005')),
            $this->getContext(),
        )->first();

        if ($parent->getChildCount() > 6) {
            return;
        }

        $path = $this->pluginPath;
        $path = str_replace('//', '/', $path);
        $path = rtrim($path, '/');
        $path .= '/Resources/product-images/';

        $mediaIds = [];

        foreach (['blue', 'green', 'red', 'white', 'yellow'] as $color) {
            $file = $path . 't-shirt-' . $color . '-600x600.jpg';

            $mediaFile = new MediaFile(
                $file,
                \mime_content_type($file),
                \pathinfo($file, PATHINFO_EXTENSION),
                \filesize($file) ?: 0
            );

            $mediaIds[$color] = $this->mediaService->saveMediaFile(
                $mediaFile,
                \pathinfo($file, PATHINFO_FILENAME),
                $this->getContext(),
                'product',
                null,
                false
            );
        }

        /** @var PropertyGroupEntity $propertyGroup */
        $propertyGroup = $propertyGroupRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('displayType', 'color'))->setLimit(1),
            $this->getContext()
        )->first();

        $yellow = Uuid::randomHex();
        $green = Uuid::randomHex();

        $insert = [
            [
                'id' => $yellow,
                'groupId' => $propertyGroup->getId(),
                'colorHexCode' => '#ffbd5d',
                'name' => [
                    'de-DE' => 'Gelb',
                    'en-GB' => 'Yellow'
                ],
            ],
            [
                'id' => $green,
                'groupId' => $propertyGroup->getId(),
                'colorHexCode' => '#3cc263',
                'name' => [
                    'de-DE' => 'Grün',
                    'en-GB' => 'Green'
                ],
            ]
        ];

        $propertyOptionRepository->create(
            $insert,
            $this->getContext()
        );

        $query = '
            UPDATE property_group_option
            SET color_hex_code = "#29b6d1"
            WHERE color_hex_code = "#0000ffff";
            
            UPDATE property_group_option
            SET color_hex_code = "#e32527"
            WHERE color_hex_code = "#ff0000ff";
            
            UPDATE property_group_option
            SET color_hex_code = "#ffffff"
            WHERE color_hex_code = "#ffffffff";
        ';
        $connection->executeStatement($query);

        /** @var PropertyGroupOptionEntity $xl */
        $xl = $propertyOptionRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('name', 'XL'))->setLimit(1),
            $this->getContext()
        )->first();

        /** @var PropertyGroupOptionEntity $m */
        $m = $propertyOptionRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('name', 'M'))->setLimit(1),
            $this->getContext()
        )->first();

        /** @var ProductEntity $parent */
        $parent = $productRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('productNumber', 'SWDEMO10005')),
            $this->getContext(),
        )->first();

        $products = [
            [
                'id' => Uuid::randomHex(),
                'parentId' => $parent->getId(),
                'productNumber' => 'SWDEMO10005.7',
                'stock' => 0,
                'options' => [
                    ['id' => $yellow],
                    ['id' => $xl->getId()]
                ]
            ],
            [
                'id' => Uuid::randomHex(),
                'parentId' => $parent->getId(),
                'productNumber' => 'SWDEMO10005.8',
                'stock' => 0,
                'options' => [
                    ['id' => $yellow],
                    ['id' => $m->getId()]
                ]
            ],
            [
                'id' => Uuid::randomHex(),
                'parentId' => $parent->getId(),
                'productNumber' => 'SWDEMO10005.9',
                'stock' => 0,
                'options' => [
                    ['id' => $green],
                    ['id' => $xl->getId()]
                ]
            ],
            [
                'id' => Uuid::randomHex(),
                'parentId' => $parent->getId(),
                'productNumber' => 'SWDEMO10005.10',
                'stock' => 0,
                'options' => [
                    ['id' => $green],
                    ['id' => $m->getId()]
                ]
            ]
        ];

        $productRepository->create($products, $this->getContext());

        $update = [
            'id' => $parent->getId(),
            'configuratorSettings' => [
                [
                    'id' => Uuid::randomHex(),
                    'optionId' => $yellow,
                    'mediaId' => $mediaIds['yellow']
                ],
                [
                    'id' => Uuid::randomHex(),
                    'optionId' => $green,
                    'mediaId' => $mediaIds['green']
                ]

            ]
        ];

        $productRepository->update([$update], $this->getContext());

        /** @var ProductEntity[] $products */
        $products = $productRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('parentId', $parent->getId())),
            $this->getContext(),
        )->getElements();

        $ids = [];

        foreach ($products as $product) {
            $ids[$product->getProductNumber()] = $product->getId();
        }

        $update = [
            [
                'id' => $ids['SWDEMO10005.1'],
                'media' => [[
                    'id' => md5('SWDEMO10005.1'),
                    'mediaId' => $mediaIds['blue'],
                    'position' => 0
                ]],
                'coverId' => md5('SWDEMO10005.1')
            ],
            [
                'id' => $ids['SWDEMO10005.2'],
                'media' => [[
                    'id' => md5('SWDEMO10005.2'),
                    'mediaId' => $mediaIds['blue'],
                    'position' => 0
                ]],
                'coverId' => md5('SWDEMO10005.2')
            ],
            [
                'id' => $ids['SWDEMO10005.3'],
                'media' => [[
                    'id' => md5('SWDEMO10005.3'),
                    'mediaId' => $mediaIds['red'],
                    'position' => 0
                ]],
                'coverId' => md5('SWDEMO10005.3')
            ],
            [
                'id' => $ids['SWDEMO10005.4'],
                'media' => [[
                    'id' => md5('SWDEMO10005.4'),
                    'mediaId' => $mediaIds['red'],
                    'position' => 0
                ]],
                'coverId' => md5('SWDEMO10005.4')
            ],
            [
                'id' => $ids['SWDEMO10005.5'],
                'media' => [[
                    'id' => md5('SWDEMO10005.5'),
                    'mediaId' => $mediaIds['white'],
                    'position' => 0
                ]],
                'coverId' => md5('SWDEMO10005.5')
            ],
            [
                'id' => $ids['SWDEMO10005.6'],
                'media' => [[
                    'id' => md5('SWDEMO10005.6'),
                    'mediaId' => $mediaIds['white'],
                    'position' => 0
                ]],
                'coverId' => md5('SWDEMO10005.6')
            ],
            [
                'id' => $ids['SWDEMO10005.7'],
                'media' => [[
                    'id' => md5('SWDEMO10005.7'),
                    'mediaId' => $mediaIds['yellow'],
                    'position' => 0
                ]],
                'coverId' => md5('SWDEMO10005.7')
            ],
            [
                'id' => $ids['SWDEMO10005.8'],
                'media' => [[
                    'id' => md5('SWDEMO10005.8'),
                    'mediaId' => $mediaIds['yellow'],
                    'position' => 0
                ]],
                'coverId' => md5('SWDEMO10005.8')
            ],
            [
                'id' => $ids['SWDEMO10005.9'],
                'media' => [[
                    'id' => md5('SWDEMO10005.9'),
                    'mediaId' => $mediaIds['green'],
                    'position' => 0
                ]],
                'coverId' => md5('SWDEMO10005.9')
            ],
            [
                'id' => $ids['SWDEMO10005.10'],
                'media' => [[
                    'id' => md5('SWDEMO10005.10'),
                    'mediaId' => $mediaIds['green'],
                    'position' => 0
                ]],
                'coverId' => md5('SWDEMO10005.10')
            ],
        ];

        $productRepository->upsert(
            $update,
            $this->getContext(),
        );
    }
}
