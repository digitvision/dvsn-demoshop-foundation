<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Storefront\Controller;

use Dvsn\DemoshopFoundation\Service\BaseService;
use Dvsn\DemoshopFoundation\Service\OrderService;
use Dvsn\DemoshopFoundation\Service\VariantService;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\Salutation\SalutationEntity;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class TestController extends StorefrontController
{
    #[Route(path: '/dvsn/demoshop-foundation/test/create-order', name: 'dvsn.demoshop-foundation.test.create-order', options: ['seo' => true], defaults: ['_httpCache' => false], methods: ['GET'])]
    public function createOrder(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): void
    {
        dd('disabled');

        /** @var OrderService $orderService */
        $orderService = $this->container->get('Dvsn\DemoshopFoundation\Service\OrderService');

        $number = $orderService->createRandomOrder();

        dd($number);
    }

    #[Route(path: '/dvsn/demoshop-foundation/test/create-home-element-alert', name: 'dvsn.demoshop-foundation.test.create-home-element-alert', options: ['seo' => true], defaults: ['_httpCache' => false], methods: ['GET'])]
    public function createHomeElementAlert(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): void
    {
        dd('disabled');

        /** @var BaseService $baseService */
        $baseService = $this->container->get('Dvsn\DemoshopFoundation\Service\BaseService');

        $baseService->createHomeElementAlert(
            'Dieser Demoshop dient der Präsentation unseres Plugins<br><strong>Produkt Konfigurator für Sets / Bundles</strong>',
            'This demo store is used to present our plugin<br><strong>Product configurator for sets / bundles</strong>',
            2,
            'success'
        );

        dd('ende');
    }

    #[Route(path: '/dvsn/demoshop-foundation/test/create-category', name: 'dvsn.demoshop-foundation.test.create-category', options: ['seo' => true], defaults: ['_httpCache' => false], methods: ['GET'])]
    public function createCategory(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): void
    {
        dd('disabled');

        /** @var BaseService $baseService */
        $baseService = $this->container->get('Dvsn\DemoshopFoundation\Service\BaseService');

        $category = $baseService->createCategory(
            'Kategorie DE',
            'Kategorie EN'
        );

        dd($category);
    }

    #[Route(path: '/dvsn/demoshop-foundation/test/parse-sql', name: 'dvsn.demoshop-foundation.test.parse-sql', options: ['seo' => true], defaults: ['_httpCache' => false], methods: ['GET'])]
    public function parseSql(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): void
    {
        dd('disabled');

        $query = <<<SQL
INSERT INTO `dvsn_configurator_stream_translation` (`dvsn_configurator_stream_id`, `language_id`, `name`, `description`, `input_placeholder`, `created_at`, `updated_at`) VALUES
(UNHEX('018D88978244727FB06B100E3D00BF6A'),	:language-de,	'Stream B',	'Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.',	NULL,	'2024-02-08 12:01:12.533',	'2024-02-08 12:13:55.548'),
(UNHEX('018D88978244727FB06B100E3D00BF6A'),	:language-en,	'Stream B',	'Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.',	NULL,	'2024-02-08 12:00:47.180',	'2024-02-08 12:05:01.834'),
(UNHEX('018D889BDA70777BAFBAB8974CF1B237'),	:language-de,	'Stream A',	NULL,	NULL,	'2024-02-08 12:14:18.433',	NULL),
(UNHEX('018D889BDA70777BAFBAB8974CF1B237'),	:language-en,	'Stream A',	NULL,	NULL,	'2024-02-08 12:05:59.054',	'2024-02-08 12:07:51.224');

INSERT INTO `dvsn_configurator_stream_product` (`id`, `position`, `stream_id`, `product_id`, `product_version_id`, `delete`, `created_at`, `updated_at`) VALUES
(UNHEX('018E9EC164997DF49BCF6CC8E7192301'),	1,	UNHEX('018D889BDA70777BAFBAB8974CF1B237'),	:product-SWDEMO10001,	UNHEX('0FA91CE3E96A4BC2BE4BD9CE752C3425'),	NULL,	'2024-04-02 12:21:20.342',	NULL),
(UNHEX('018E9EC184097F078FFCAB680C9974FF'),	2,	UNHEX('018D889BDA70777BAFBAB8974CF1B237'),	:product-SWDEMO10007.2,	UNHEX('0FA91CE3E96A4BC2BE4BD9CE752C3425'),	NULL,	'2024-04-02 12:21:20.342',	NULL),
(UNHEX('018E9EC1AAD473B291C966448C52212E'),	3,	UNHEX('018D889BDA70777BAFBAB8974CF1B237'),	:product-SWDEMO10005.4,	UNHEX('0FA91CE3E96A4BC2BE4BD9CE752C3425'),	NULL,	'2024-04-02 12:21:20.342',	NULL);

INSERT INTO `dvsn_configurator_stream_preselected_product` (`id`, `stream_id`, `product_id`, `product_version_id`, `delete`, `created_at`, `updated_at`) VALUES
(UNHEX('018E9EC1E1C67F8ABD1CC83F03EDEB6F'),	UNHEX('018D889BDA70777BAFBAB8974CF1B237'),	:product-SWDEMO10001,	UNHEX('0FA91CE3E96A4BC2BE4BD9CE752C3425'),	NULL,	'2024-04-02 12:21:37.992',	NULL),
(UNHEX('018E9EC1F1447F21801E06FE47AD9C4E'),	UNHEX('018D889BDA70777BAFBAB8974CF1B237'),	:product-SWDEMO10007.2,	UNHEX('0FA91CE3E96A4BC2BE4BD9CE752C3425'),	NULL,	'2024-04-02 12:21:37.992',	NULL),
(UNHEX('018E9EC1F28C74A59EFFC23E7470015E'),	UNHEX('018D889BDA70777BAFBAB8974CF1B237'),	:product-SWDEMO10005.4,	UNHEX('0FA91CE3E96A4BC2BE4BD9CE752C3425'),	NULL,	'2024-04-02 12:21:37.992',	NULL);
SQL;

        /** @var BaseService $baseService */
        $baseService = $this->container->get('Dvsn\DemoshopFoundation\Service\BaseService');

        $query = $baseService->parseSql($query);

        echo nl2br($query);
        die();
    }

    #[Route(path: '/dvsn/demoshop-foundation/test/update-tshirt', name: 'dvsn.demoshop-foundation.test.update-tshirt', options: ['seo' => true], defaults: ['_httpCache' => false], methods: ['GET'])]
    public function updateTshirt(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): void
    {
        dd('disabled');

        /** @var VariantService $variantService */
        $variantService = $this->container->get('Dvsn\DemoshopFoundation\Service\VariantService');

        dd($variantService->updateTshirt());
    }

    #[Route(path: '/dvsn/demoshop-foundation/test/parse-array', name: 'dvsn.demoshop-foundation.test.parse-array', options: ['seo' => true], defaults: ['_httpCache' => false], methods: ['GET'])]
    public function parseArray(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): void
    {
        dd('disabled');

        /** @var BaseService $baseService */
        $baseService = $this->container->get('Dvsn\DemoshopFoundation\Service\BaseService');

        /** @var EntityRepository $configuratorRepository */
        $configuratorRepository = $this->container->get('dvsn_configurator.repository');

        $configuratorA = [
            'id' => Uuid::randomHex(),
            'name' => 'Konfigurator A',
            'position' => 'content',
            'listingPrice' => 'preselection',
            'summary' => false,
            'summaryExtended' => false,
            'free' => false,
            'rebate' => 0,
            'collapsibleStreams' => false,
            'hideProductTabs' => false,
            'streams' => [
                [
                    'id' => Uuid::randomHex(),
                    'position' => 1,
                    'free' => false,
                    'freeShowPrices' => false,
                    'hidePrices' => false,
                    'quantity' => 1,
                    'quantitySelection' => false,
                    'relativePrices' => false,
                    'mandatory' => false,
                    'multiple' => true,
                    'fullySelectable' => false,
                    'fanOutVariants' => false,
                    'variantPropertiesAsName' => false,
                    'template' => 'list',
                    'setStatus' => false,
                    'setQuantity' => 1,
                    'setQuantityY' => null,
                    'setBehaviour' => 'free',
                    'productOrder' => 'name:ASC',
                    'productLimit' => 25,
                    'productSource' => 'selection',
                    'conditionStatus' => false,
                    'conditionVisibility' => 'hidden',
                    'disableSelection' => false,
                    'displayGalleryCompact' => false,
                    'displayListImages' => false,
                    'inputStatus' => false,
                    'inputMandatory' => false,
                    'productStreamId' => null,
                    'delete' => null,
                    'name' => [
                        'de-DE' => 'Produkte als Liste',
                        'en-GB' => 'Produkte als Liste'
                    ],
                    'description' => [
                        'de-DE' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.',
                        'en-GB' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.'
                    ],
                    'products' => [
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 1,
                            'productId' => 'product-SWDEMO10001'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 2,
                            'productId' => 'product-SWDEMO10002'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 3,
                            'productId' => 'product-SWDEMO100013'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 4,
                            'productId' => 'product-SWDEMO10006'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 5,
                            'productId' => 'product-SWDEMO10005'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 6,
                            'productId' => 'product-SWDEMO10007'
                        ],
                    ],
                    'preselectedProducts' => [
                        [
                            'id' => Uuid::randomHex(),
                            'productId' => 'product-SWDEMO10001'
                        ]
                    ],
                    'blacklistedProducts' => [],
                    'productPrices' => [],
                    'conditions' => []
                ],
                [
                    'id' => Uuid::randomHex(),
                    'position' => 2,
                    'free' => false,
                    'freeShowPrices' => false,
                    'hidePrices' => false,
                    'quantity' => 1,
                    'quantitySelection' => true,
                    'relativePrices' => false,
                    'mandatory' => false,
                    'multiple' => true,
                    'fullySelectable' => true,
                    'fanOutVariants' => false,
                    'variantPropertiesAsName' => false,
                    'template' => 'slider',
                    'setStatus' => false,
                    'setQuantity' => 1,
                    'setQuantityY' => null,
                    'setBehaviour' => 'free',
                    'productOrder' => 'name:ASC',
                    'productLimit' => 25,
                    'productSource' => 'selection',
                    'conditionStatus' => false,
                    'conditionVisibility' => 'hidden',
                    'disableSelection' => false,
                    'displayGalleryCompact' => false,
                    'displayListImages' => false,
                    'inputStatus' => false,
                    'inputMandatory' => false,
                    'productStreamId' => null,
                    'delete' => null,
                    'name' => [
                        'de-DE' => 'Produkte als Slider, mit Stückzahl, ohne Button',
                        'en-GB' => 'Produkte als Slider, mit Stückzahl, ohne Button'
                    ],
                    'products' => [
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 1,
                            'productId' => 'product-SWDEMO10001'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 2,
                            'productId' => 'product-SWDEMO100013'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 3,
                            'productId' => 'product-SWDEMO10005'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 4,
                            'productId' => 'product-SWDEMO10007'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 5,
                            'productId' => 'product-SWDEMO10006'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 6,
                            'productId' => 'product-SWDEMO10002'
                        ],
                    ],
                    'preselectedProducts' => [
                        [
                            'id' => Uuid::randomHex(),
                            'productId' => 'product-SWDEMO100013'
                        ]
                    ],
                    'blacklistedProducts' => [],
                    'productPrices' => [],
                    'conditions' => []
                ],
                [
                    'id' => Uuid::randomHex(),
                    'position' => 3,
                    'free' => false,
                    'freeShowPrices' => false,
                    'hidePrices' => true,
                    'quantity' => 1,
                    'quantitySelection' => false,
                    'relativePrices' => false,
                    'mandatory' => true,
                    'multiple' => false,
                    'fullySelectable' => false,
                    'fanOutVariants' => false,
                    'variantPropertiesAsName' => true,
                    'template' => 'gallery',
                    'setStatus' => false,
                    'setQuantity' => 1,
                    'setQuantityY' => null,
                    'setBehaviour' => 'free',
                    'productOrder' => 'name:ASC',
                    'productLimit' => 25,
                    'productSource' => 'selection',
                    'conditionStatus' => false,
                    'conditionVisibility' => 'hidden',
                    'disableSelection' => false,
                    'displayGalleryCompact' => true,
                    'displayListImages' => false,
                    'inputStatus' => false,
                    'inputMandatory' => false,
                    'productStreamId' => null,
                    'delete' => null,
                    'name' => [
                        'de-DE' => 'Produkte als Galerie, explizite Varianten, ohne Preis, Pflichtfeld',
                        'en-GB' => 'Produkte als Galerie, explizite Varianten, ohne Preis, Pflichtfeld'
                    ],
                    'products' => [
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 1,
                            'productId' => 'product-SWDEMO10005.2'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 2,
                            'productId' => 'product-SWDEMO10005.4'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 3,
                            'productId' => 'product-SWDEMO10005.9'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 4,
                            'productId' => 'product-SWDEMO10005.7'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 5,
                            'productId' => 'product-SWDEMO10005.6'
                        ],
                    ],
                    'preselectedProducts' => [],
                    'blacklistedProducts' => [],
                    'productPrices' => [],
                    'conditions' => []
                ],
                [
                    'id' => Uuid::randomHex(),
                    'position' => 4,
                    'free' => false,
                    'freeShowPrices' => false,
                    'hidePrices' => false,
                    'quantity' => 1,
                    'quantitySelection' => true,
                    'relativePrices' => false,
                    'mandatory' => false,
                    'multiple' => true,
                    'fullySelectable' => true,
                    'fanOutVariants' => false,
                    'variantPropertiesAsName' => false,
                    'template' => 'slider',
                    'setStatus' => true,
                    'setQuantity' => 3,
                    'setQuantityY' => null,
                    'setBehaviour' => 'fixed',
                    'productOrder' => 'name:ASC',
                    'productLimit' => 25,
                    'productSource' => 'selection',
                    'conditionStatus' => false,
                    'conditionVisibility' => 'hidden',
                    'disableSelection' => false,
                    'displayGalleryCompact' => false,
                    'displayListImages' => false,
                    'inputStatus' => false,
                    'inputMandatory' => false,
                    'productStreamId' => null,
                    'delete' => null,
                    'name' => [
                        'de-DE' => 'Produkte als Slider, festes Set (3 Stück wählen)',
                        'en-GB' => 'Produkte als Slider, festes Set (3 Stück wählen)'
                    ],
                    'products' => [
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 1,
                            'productId' => 'product-SWDEMO10001'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 2,
                            'productId' => 'product-SWDEMO100013'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 3,
                            'productId' => 'product-SWDEMO10005'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 4,
                            'productId' => 'product-SWDEMO10007'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 5,
                            'productId' => 'product-SWDEMO10006'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 6,
                            'productId' => 'product-SWDEMO10002'
                        ],
                    ],
                    'preselectedProducts' => [],
                    'blacklistedProducts' => [],
                    'productPrices' => [],
                    'conditions' => []
                ],
                [
                    'id' => Uuid::randomHex(),
                    'position' => 5,
                    'free' => false,
                    'freeShowPrices' => false,
                    'hidePrices' => false,
                    'quantity' => 5,
                    'quantitySelection' => false,
                    'relativePrices' => false,
                    'mandatory' => false,
                    'multiple' => false,
                    'fullySelectable' => true,
                    'fanOutVariants' => false,
                    'variantPropertiesAsName' => false,
                    'template' => 'list',
                    'setStatus' => false,
                    'setQuantity' => 1,
                    'setQuantityY' => null,
                    'setBehaviour' => 'free',
                    'productOrder' => 'name:ASC',
                    'productLimit' => 25,
                    'productSource' => 'selection',
                    'conditionStatus' => false,
                    'conditionVisibility' => 'hidden',
                    'disableSelection' => false,
                    'displayGalleryCompact' => false,
                    'displayListImages' => true,
                    'inputStatus' => false,
                    'inputMandatory' => false,
                    'productStreamId' => null,
                    'delete' => null,
                    'name' => [
                        'de-DE' => 'Produkte als Liste, keine Mehrfachauswahl, feste Stückzahl, mit Bild',
                        'en-GB' => 'Produkte als Liste, keine Mehrfachauswahl, feste Stückzahl, mit Bild'
                    ],
                    'products' => [
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 1,
                            'productId' => 'product-SWDEMO10001'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 2,
                            'productId' => 'product-SWDEMO100013'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 3,
                            'productId' => 'product-SWDEMO10005'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 4,
                            'productId' => 'product-SWDEMO10007'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 5,
                            'productId' => 'product-SWDEMO10006'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 6,
                            'productId' => 'product-SWDEMO10002'
                        ],
                    ],
                    'preselectedProducts' => [],
                    'blacklistedProducts' => [],
                    'productPrices' => [],
                    'conditions' => []
                ],
                [
                    'id' => Uuid::randomHex(),
                    'position' => 6,
                    'free' => false,
                    'freeShowPrices' => false,
                    'hidePrices' => false,
                    'quantity' => 1,
                    'quantitySelection' => false,
                    'relativePrices' => false,
                    'mandatory' => true,
                    'multiple' => false,
                    'fullySelectable' => false,
                    'fanOutVariants' => false,
                    'variantPropertiesAsName' => false,
                    'template' => 'slider',
                    'setStatus' => false,
                    'setQuantity' => 1,
                    'setQuantityY' => null,
                    'setBehaviour' => 'free',
                    'productOrder' => 'name:ASC',
                    'productLimit' => 25,
                    'productSource' => 'selection',
                    'conditionStatus' => false,
                    'conditionVisibility' => 'hidden',
                    'disableSelection' => false,
                    'displayGalleryCompact' => false,
                    'displayListImages' => true,
                    'inputStatus' => true,
                    'inputMandatory' => true,
                    'productStreamId' => null,
                    'delete' => null,
                    'name' => [
                        'de-DE' => 'Produkte als Slider, mit Eingabefeld',
                        'en-GB' => 'Produkte als Slider, mit Eingabefeld'
                    ],
                    'inputPlaceholder' => [
                        'de-DE' => 'Ihren Namen eingeben...',
                        'en-GB' => 'Ihren Namen eingeben...'
                    ],
                    'products' => [
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 1,
                            'productId' => 'product-SWDEMO10001'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 2,
                            'productId' => 'product-SWDEMO100013'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 3,
                            'productId' => 'product-SWDEMO10005'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 4,
                            'productId' => 'product-SWDEMO10007'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 5,
                            'productId' => 'product-SWDEMO10006'
                        ],
                        [
                            'id' => Uuid::randomHex(),
                            'position' => 6,
                            'productId' => 'product-SWDEMO10002'
                        ],
                    ],
                    'preselectedProducts' => [],
                    'blacklistedProducts' => [],
                    'productPrices' => [],
                    'conditions' => []
                ],
            ]
        ];

        $configuratorA = $baseService->parseArray($configuratorA);
        $configuratorRepository->create([$configuratorA], $context);

        dd($configuratorA);
    }

    #[Route(path: '/dvsn/demoshop-foundation/test/create-customer', name: 'dvsn.demoshop-foundation.test.create-customer', options: ['seo' => true], defaults: ['_httpCache' => false], methods: ['GET'])]
    public function createCustomer(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): void
    {
        dd('disabled');

        /** @var EntityRepository $customerRepository */
        $customerRepository = $this->container->get('customer.repository');

        /** @var EntityRepository $customerGroupRepository */
        $customerGroupRepository = $this->container->get('customer_group.repository');

        /** @var BaseService $baseService */
        $baseService = $this->container->get('Dvsn\DemoshopFoundation\Service\BaseService');

        /** @var EntityRepository $salesChannelRepository */
        $salesChannelRepository = $this->container->get('sales_channel.repository');

        /** @var EntityRepository $salutationRepository */
        $salutationRepository = $this->container->get('salutation.repository');

        /** @var EntityRepository $salutationRepository */
        $countryRepository = $this->container->get('country.repository');

        $languages = $baseService->getLanguages();

        /** @var CustomerGroupEntity $customerGroup */
        $customerGroup = $customerGroupRepository->search(
            (new Criteria())->setLimit(1),
            $context
        )->first();

        /** @var SalesChannelEntity $salesChannel */
        $salesChannel = $salesChannelRepository->search(
            (new Criteria())->addAssociations(['domains'])->addFilter(new EqualsFilter('typeId', Defaults::SALES_CHANNEL_TYPE_STOREFRONT))->setLimit(1),
            $context
        )->first();

        /** @var SalutationEntity $salutation */
        $salutation = $salutationRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('salutationKey', 'mr'))->setLimit(1),
            $context
        )->first();

        /** @var CountryEntity $country */
        $country = $countryRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('iso', 'DE'))->setLimit(1),
            $context
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


        $customerRepository->create(
            [$customer],
            $context
        );

        dd("ende");
    }

}
