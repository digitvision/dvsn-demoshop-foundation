<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Setup\Helper;

use Shopware\Core\Framework\Uuid\Uuid;

trait SalesChannelTrait
{
    protected function getSalesChannels(): array
    {
        $arr = $this->connection->fetchAllAssociative('
            SELECT id
            FROM sales_channel
        ');

        $salesChannels = [];

        foreach ($arr as $salesChannel) {
            $salesChannels[] = Uuid::fromBytesToHex($salesChannel['id']);
        }

        return $salesChannels;
    }
}
