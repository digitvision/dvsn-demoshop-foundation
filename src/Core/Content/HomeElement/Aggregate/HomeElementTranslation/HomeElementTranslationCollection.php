<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Core\Content\HomeElement\Aggregate\HomeElementTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class HomeElementTranslationCollection extends EntityCollection
{
    public function filterByLanguageId(string $id): self
    {
        return $this->filter(function (HomeElementTranslationEntity $entity) use ($id) {
            return $entity->getLanguageId() === $id;
        });
    }

    protected function getExpectedClass(): string
    {
        return HomeElementTranslationEntity::class;
    }
}
