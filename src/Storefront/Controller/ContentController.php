<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Storefront\Controller;

use Dvsn\DemoshopFoundation\Storefront\Page\Content\ImprintPage;
use Dvsn\DemoshopFoundation\Storefront\Page\Content\PrivacyPolicyPage;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Shopware\Storefront\Page\Page;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class ContentController extends StorefrontController
{
    public function __construct(
        private readonly GenericPageLoaderInterface $genericPageLoader
    ) {
    }

    #[Route(path: '/imprint', name: 'frontend.dvsn.demoshop-foundation.content.imprint', options: ['seo' => true], defaults: ['_httpCache' => false], methods: ['GET'])]
    public function imprint(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): Response
    {
        /** @var Page $page */
        $page = $this->genericPageLoader->load($request, $salesChannelContext);

        /** @var ImprintPage $page */
        $page = ImprintPage::createFrom($page);

        $page->getMetaInformation()->setMetaTitle($this->trans('dvsn-demoshop-foundation.content.imprint.meta.title'));

        return $this->renderStorefront('@Storefront/storefront/page/dvsn/demoshop-foundation/content/imprint-en.html.twig', ['page' => $page]);
    }

    #[Route(path: '/privacy-policy', name: 'frontend.dvsn.demoshop-foundation.content.privacy-policy', options: ['seo' => true], defaults: ['_httpCache' => false], methods: ['GET'])]
    public function privacyPolicy(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): Response
    {
        /** @var Page $page */
        $page = $this->genericPageLoader->load($request, $salesChannelContext);

        /** @var PrivacyPolicyPage $page */
        $page = PrivacyPolicyPage::createFrom($page);

        $page->getMetaInformation()->setMetaTitle($this->trans('dvsn-demoshop-foundation.content.privacy-policy.meta.title'));

        return $this->renderStorefront('@Storefront/storefront/page/dvsn/demoshop-foundation/content/privacy-policy-en.html.twig', ['page' => $page]);
    }

    #[Route(path: '/impressum', name: 'frontend.dvsn.demoshop-foundation.content.impressum', options: ['seo' => true], defaults: ['_httpCache' => false], methods: ['GET'])]
    public function impressum(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): Response
    {
        /** @var Page $page */
        $page = $this->genericPageLoader->load($request, $salesChannelContext);

        /** @var ImprintPage $page */
        $page = ImprintPage::createFrom($page);

        $page->getMetaInformation()->setMetaTitle($this->trans('dvsn-demoshop-foundation.content.imprint.meta.title'));

        return $this->renderStorefront('@Storefront/storefront/page/dvsn/demoshop-foundation/content/imprint-de.html.twig', ['page' => $page]);
    }

    #[Route(path: '/datenschutz', name: 'frontend.dvsn.demoshop-foundation.content.datenschutz', options: ['seo' => true], defaults: ['_httpCache' => false], methods: ['GET'])]
    public function datenschutz(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): Response
    {
        /** @var Page $page */
        $page = $this->genericPageLoader->load($request, $salesChannelContext);

        /** @var PrivacyPolicyPage $page */
        $page = PrivacyPolicyPage::createFrom($page);

        $page->getMetaInformation()->setMetaTitle($this->trans('dvsn-demoshop-foundation.content.privacy-policy.meta.title'));

        return $this->renderStorefront('@Storefront/storefront/page/dvsn/demoshop-foundation/content/privacy-policy-de.html.twig', ['page' => $page]);
    }
}
