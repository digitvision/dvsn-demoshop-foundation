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
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageEntity;
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


    }

    private function installCategories()
    {
        $enLanguageId = Defaults::LANGUAGE_SYSTEM;
        $deLanguageId = null;

        /** @var EntityRepository $languageRepository */
        $languageRepository = $this->container->get('language.repository');

        /** @var LanguageEntity $deLanguage */
        $deLanguage = $languageRepository->search(
            (new Criteria())->addAssociations(['locale'])->addFilter(new EqualsFilter('locale.code', 'de-DE')),
            $this->context->getContext()
        )->first();

        $deLanguageId = $deLanguage->getId();



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
                    'languageId' => $enLanguageId,
                    'name' => 'Catalogue #2'
                ],
                [
                    'languageId' => $deLanguageId,
                    'name' => 'Katalog #2'
                ]
            ]
        ];

        $categoryRepository->upsert([$catalogCategory], $this->context->getContext());

        $parentCategory = [
            'id' => Uuid::randomHex(),
            'parent' => $catalogCategory['id'],
            'active' => true,
            'visible' => true,
            'type' => 'folder',
            'translations' => [
                [
                    'languageId' => $enLanguageId,
                    'name' => 'Legal information'
                ],
                [
                    'languageId' => $deLanguageId,
                    'name' => 'Rechtliche Hinweise'
                ]
            ]
        ];

        $categoryRepository->upsert([$parentCategory], $this->context->getContext());

        $imprintCategory = [
            'id' => Uuid::randomHex(),
            'parent' => $parentCategory['id'],
            'active' => true,
            'visible' => true,
            'type' => 'link',
            'translations' => [
                [
                    'languageId' => $enLanguageId,
                    'name' => 'Imprint',
                    'linkType' => 'external',
                    'externalLink' => '/imprint'
                ],
                [
                    'languageId' => $deLanguageId,
                    'name' => 'Impressum',
                    'linkType' => 'external',
                    'externalLink' => '/impressum'
                ]
            ]
        ];

        $categoryRepository->upsert([$imprintCategory], $this->context->getContext());

    }
}
