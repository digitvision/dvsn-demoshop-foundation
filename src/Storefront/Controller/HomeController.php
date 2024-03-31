<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Storefront\Controller;

use Dvsn\DemoshopFoundation\Core\Content\HomeElement\HomeElementEntity;
use Dvsn\DemoshopFoundation\Storefront\Page\Home\IndexPage;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Shopware\Storefront\Page\Page;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class HomeController extends StorefrontController
{
    private array $defaults = [
        'alert' => [
            'payload' => [],
            'translatablePayload' => [
                'content' => '---'
            ]
        ],
        'content' => [
            'payload' => [],
            'translatablePayload' => [
                'cls' => 'dvsn-demoshop-foundation-home-element-content-container',
                'content' => '---'
            ]
        ],
        'products' => [
            'payload' => [
                'ids' => [],
                'listingColumns' => 'col-sm-6 col-lg-4 col-xl-3',
                'boxLayout' => 'standard'
            ],
            'translatablePayload' => []
        ]
    ];

    public function __construct(
        private readonly EntityRepository $homeElementRepository,
        private readonly SalesChannelRepository $salesChannelProductRepository,
        private readonly GenericPageLoaderInterface $genericPageLoader
    ) {
    }

    #[Route(path: '/', name: 'frontend.home.page', options: ['seo' => true], defaults: ['_httpCache' => false], methods: ['GET'])]
    public function index(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): Response
    {
        $elementsCollection = $this->homeElementRepository->search(
            (new Criteria())->addSorting(new FieldSorting('position')),
            $context
        )->getEntities();

        /** @var HomeElementEntity $element */
        foreach ($elementsCollection->getElements() as $element) {
            $element->setPayload(array_merge($this->defaults[$element->getType()]['payload'], $element->getPayload()));

            $translated = $element->getTranslated();
            $translated['translatablePayload'] = array_merge($this->defaults[$element->getType()]['translatablePayload'], $translated['translatablePayload']);

            $element->setTranslated($translated);

            switch ($element->getType()) {
                case 'products':
                    $products = $this->salesChannelProductRepository->search(
                        (new Criteria($element->getPayload()['ids'])),
                        $salesChannelContext
                    );

                    $element->addExtension(
                        'products',
                        new ArrayStruct(['products' => $products])
                    );

                    break;
            }
        }

        /** @var Page $page */
        $page = $this->genericPageLoader->load($request, $salesChannelContext);

        /** @var IndexPage $page */
        $page = IndexPage::createFrom($page);

        $page->assign([
            'elements' => $elementsCollection->getElements()
        ]);

        $page->getMetaInformation()->setMetaTitle($this->trans('dvsn-demoshop-foundation.home.meta.title'));

        return $this->renderStorefront('@Storefront/storefront/page/dvsn/demoshop-foundation/home/index.html.twig', ['page' => $page]);
    }

    #[Route(path: '/dvsn/demoshop-foundation/home/create-test-data', name: 'frontend.dvsn.demoshop-foundation.home.create-test-data', options: ['seo' => true], defaults: ['_httpCache' => false], methods: ['GET'])]
    public function createTestData(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): Response
    {
        return $this->redirectToRoute('frontend.home.page');

        $install = [
            [
                'id' => 'c5cb35214568c251299d79c10c22ebcd',
                'position' => 1,
                'type' => 'alert',
                'payload' => ['type' => 'info'],
                'translations' => [[
                    'languageId' => '2fbb5fe2e29a4d70aa5854ce7ce3e20b',
                    'translatablePayload' => ['content' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.']
                ]],
            ],
            [
                'id' => 'b5cb35214568c251299d79c10c22ebcd',
                'position' => 2,
                'type' => 'content',
                'payload' => [],
                'translations' => [[
                    'languageId' => '2fbb5fe2e29a4d70aa5854ce7ce3e20b',
                    'translatablePayload' => ['content' => '<h4>Lorem ipsum</h4>Dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.']
                ]],
            ],
            [
                'id' => 'a5cb35214568c251299d79c10c22ebcd',
                'position' => 3,
                'type' => 'products',
                'payload' => ['ids' => ['2a88d9b59d474c7e869d8071649be43c', '018e1f035e5971609a82c713fda4bc21']],
                'translations' => [[
                    'languageId' => '2fbb5fe2e29a4d70aa5854ce7ce3e20b',
                    'translatablePayload' => []
                ]],
            ],
        ];

        $this->homeElementRepository->upsert($install, $context);

        return $this->redirectToRoute('frontend.home.page');
    }
}
