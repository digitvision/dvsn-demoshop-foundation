<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Core\Content\HomeElement;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class HomeElementCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return HomeElementEntity::class;
    }
}
