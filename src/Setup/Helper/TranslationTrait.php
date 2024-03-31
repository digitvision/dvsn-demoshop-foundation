<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Setup\Helper;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;

trait TranslationTrait
{
    protected function parseTranslations(array $data): array
    {
        $arr = $this->connection->fetchAllAssociative('
            SELECT lang.id, loc.code
            FROM language lang
            INNER JOIN locale loc ON lang.translation_code_id = loc.id
        ');

        $languages = [];

        foreach ($arr as $language) {
            $languages[$language['code']] = Uuid::fromBytesToHex($language['id']);
        }

        $translations = [];

        foreach ($languages as $locale => $languageId) {
            $translation = (substr_count($locale, 'de-') > 0)
                ? $data['translations']['de']
                : $data['translations']['en'];

            if ($languageId === Defaults::LANGUAGE_SYSTEM) {
                $data = array_merge($data, $translation);
            }

            $translation['language'] = ['id' => $languageId];

            $translations[] = $translation;
        }

        $data['translations'] = $translations;

        return $data;
    }
}
