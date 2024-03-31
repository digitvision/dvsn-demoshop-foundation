<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Core\Content\HomeElement;

use Dvsn\DemoshopFoundation\Core\Content\HomeElement\Aggregate\HomeElementTranslation\HomeElementTranslationCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class HomeElementEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $payload;

    /**
     * @var array
     */
    protected $translatablePayload;

    /**
     * @var HomeElementTranslationCollection
     */
    protected $translations;

    public function getPosition(): int
    {
        return $this->position;
    }



    public function setPosition(int $position): void
    {
        $this->position = $position;
    }



    public function getType(): string
    {
        return $this->type;
    }



    public function setType(string $type): void
    {
        $this->type = $type;
    }



    public function getPayload(): array
    {
        return $this->payload;
    }



    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }



    public function getTranslatablePayload(): array
    {
        return $this->translatablePayload;
    }



    public function setTranslatablePayload(array $translatablePayload): void
    {
        $this->translatablePayload = $translatablePayload;
    }



    public function getTranslations(): HomeElementTranslationCollection
    {
        return $this->translations;
    }



    public function setTranslations(HomeElementTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

}
