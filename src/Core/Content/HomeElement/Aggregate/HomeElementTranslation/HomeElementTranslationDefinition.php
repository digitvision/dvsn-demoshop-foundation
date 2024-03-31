<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Core\Content\HomeElement\Aggregate\HomeElementTranslation;

use Dvsn\DemoshopFoundation\Core\Content\HomeElement\HomeElementDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class HomeElementTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'dvsn_demoshop_foundation_home_element_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return HomeElementTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return HomeElementTranslationEntity::class;
    }

    public function getParentDefinitionClass(): string
    {
        return HomeElementDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new JsonField('translatable_payload', 'translatablePayload', [], []))->addFlags(new Required()),
        ]);
    }
}
