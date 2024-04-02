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
    public function test(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): void
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
}
