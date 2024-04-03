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
use Dvsn\DemoshopFoundation\Service\VariantService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class TestController extends StorefrontController
{
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
(UNHEX('018D88978244727FB06B100E3D00BF6A'),	:de,	'Stream B',	'Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.',	NULL,	'2024-02-08 12:01:12.533',	'2024-02-08 12:13:55.548'),
(UNHEX('018D88978244727FB06B100E3D00BF6A'),	:en,	'Stream B',	'Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.',	NULL,	'2024-02-08 12:00:47.180',	'2024-02-08 12:05:01.834'),
(UNHEX('018D889BDA70777BAFBAB8974CF1B237'),	:de,	'Stream A',	NULL,	NULL,	'2024-02-08 12:14:18.433',	NULL),
(UNHEX('018D889BDA70777BAFBAB8974CF1B237'),	:en,	'Stream A',	NULL,	NULL,	'2024-02-08 12:05:59.054',	'2024-02-08 12:07:51.224');

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

    #[Route(path: 'test', name: 'test', options: ['seo' => true], defaults: ['_httpCache' => false], methods: ['GET'])]
    public function test(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): void
    {
        dd('disabled');

        /** @var VariantService $variantService */
        $variantService = $this->container->get('Dvsn\DemoshopFoundation\Service\VariantService');

        dd($variantService->updateTshirt());
    }
}
