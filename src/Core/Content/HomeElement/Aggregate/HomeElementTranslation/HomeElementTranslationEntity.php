<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Core\Content\HomeElement\Aggregate\HomeElementTranslation;

use Dvsn\DemoshopFoundation\Core\Content\HomeElement\HomeElementEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class HomeElementTranslationEntity extends TranslationEntity
{

    /**
     * @var array
     */
    protected $translatablePayload;

    /**
     * @var HomeElementEntity
     */
    protected $homeElement;

    /**
     * @var string
     */
    protected $homeElementId;



    public function getTranslatablePayload(): array
    {
        return $this->translatablePayload;
    }



    public function setTranslatablePayload(array $translatablePayload): void
    {
        $this->translatablePayload = $translatablePayload;
    }



    public function getHomeElement(): HomeElementEntity
    {
        return $this->homeElement;
    }



    public function setHomeElement(HomeElementEntity $homeElement): void
    {
        $this->homeElement = $homeElement;
    }



    public function getHomeElementId(): string
    {
        return $this->homeElementId;
    }



    public function setHomeElementId(string $homeElementId): void
    {
        $this->homeElementId = $homeElementId;
    }




}
