<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Service;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryInformation;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\QuantityInformation;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\SalesChannel\AbstractCartOrderRoute;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class OrderService
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly AbstractCartOrderRoute $cartOrderRoute,
        private readonly AbstractSalesChannelContextFactory $salesChannelContextFactory,
        private readonly EntityRepository $customerRepository,
        private readonly EntityRepository $salesChannelRepository,
        private readonly BaseService $baseService,
        private readonly QuantityPriceCalculator $quantityPriceCalculator
    ) {
    }

    public function createRandomOrder(?int $productQuantity = null, ?string $customerId = null, ?string $salesChannelId = null): ?OrderEntity
    {
        $context = Context::createDefaultContext();

        if ($customerId === null) {
            /** @var CustomerEntity $customer */
            $customer = $this->customerRepository->search(
                (new Criteria())
                    ->addFilter(new EqualsFilter('email', 'max@mustermann.de')),
                $context
            )->first();

            $customerId = $customer->getId();
        }

        if ($salesChannelId === null) {
            /** @var SalesChannelEntity $salesChannel */
            $salesChannel = $this->salesChannelRepository->search(
                (new Criteria())->addAssociations(['domains'])->addFilter(new EqualsFilter('typeId', Defaults::SALES_CHANNEL_TYPE_STOREFRONT))->setLimit(1),
                $context
            )->first();

            $salesChannelId = $salesChannel->getId();
        }

        /** @var CustomerEntity $customer */
        $customer = $this->customerRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('id', $customerId)),
            $context
        )->first();

        $salesChannelContext = $this->getSalesChannelContext(
            $customer,
            $salesChannelId
        );

        if ($productQuantity === null) {
            $productQuantity = rand(1, 5);
        }

        /** @var SalesChannelProductEntity[] $products */
        $products = $this->baseService->getRandomProducts($productQuantity, $salesChannelContext);

        $lineItems = [];

        foreach ($products as $product) {
            $lineItems[] = $this->getLineItem($product, rand(1, 5), $salesChannelContext);
        }

        try {
            $cart = new Cart(
                $salesChannelContext->getToken()
            );

            $cart = $this->cartService->add(
                $cart,
                $lineItems,
                $salesChannelContext
            );

            $this->cartService->recalculate(
                $cart,
                $salesChannelContext
            );

            $response = $this->cartOrderRoute->order(
                $cart,
                $salesChannelContext,
                new RequestDataBag()
            );
        } catch (\Exception $exception) {
            return null;
        }

        return $response->getOrder();
    }

    private function getLineItem(SalesChannelProductEntity $product, int $quantity, SalesChannelContext $salesChannelContext): LineItem
    {
        $lineItem = new LineItem(
            $product->getId(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $product->getId(),
            $quantity
        );

        $lineItem->setLabel($product->getTranslated()['name'])
            ->setGood(true)
            ->setStackable(true)
            ->setRemovable(true)
            ->setDescription(null)
            ->setQuantityInformation(new QuantityInformation())
            ->setDeliveryInformation(new DeliveryInformation(
                $product->getStock(),
                $product->getWeight(),
                $product->getShippingFree() === true,
                $product->getRestockTime(),
                null,
                $product->getHeight(),
                $product->getWidth(),
                $product->getLength()
            ))
            ->setCover($product->getCover()?->getMedia())
            ->setPayload([
                'isCloseout' => $product->getIsCloseout(),
                'customFields' => $product->getCustomFields(),
                'purchasePrices' => $product->getPurchasePrices() ? \json_encode($product->getPurchasePrices()) : null,
                'productNumber' => $product->getProductNumber(),
                'manufacturerId' => $product->getManufacturerId(),
                'taxId' => $product->getTaxId(),
                'tagIds' => $product->getTagIds(),
                'categoryIds' => $product->getCategoryTree(),
                'propertyIds' => $product->getPropertyIds(),
                'optionIds' => $product->getOptionIds(),
                'options' => $product->getVariation(),
                'streamIds' => $product->getStreamIds(),
                'parentId' => $product->getParentId(),
                'stock' => $product->getStock(),
                'markAsTopseller' => $product->getMarkAsTopseller(),
                'createdAt' => $product->getCreatedAt() ? $product->getCreatedAt()->format(Defaults::STORAGE_DATE_TIME_FORMAT) : null,
            ])
            ->setStates($product->getStates());

        $definition = new QuantityPriceDefinition(
            $this->getCalculatedProductPrice(
                $product,
                $quantity
            )->getUnitPrice(),
            $salesChannelContext->buildTaxRules($product->getTaxId()),
            $quantity
        );

        $lineItem->setPriceDefinition(
            $definition
        );

        $lineItem->setPrice(
            $this->quantityPriceCalculator->calculate(
                $definition,
                $salesChannelContext
            )
        );

        return $lineItem;
    }

    private function getCalculatedProductPrice(SalesChannelProductEntity $product, int $quantity): CalculatedPrice
    {
        if ($product->getCalculatedPrices()->count() === 0) {
            return $product->getCalculatedPrice();
        }

        $price = $product->getCalculatedPrice();

        foreach ($product->getCalculatedPrices() as $price) {
            if ($quantity <= $price->getQuantity()) {
                break;
            }
        }

        return $price;
    }

    private function getSalesChannelContext(CustomerEntity $customer, string $salesChannelId): SalesChannelContext
    {
        return $this->salesChannelContextFactory->create(
            Uuid::randomHex(),
            $salesChannelId,
            [
                SalesChannelContextService::CUSTOMER_ID => $customer->getId(),
                SalesChannelContextService::BILLING_ADDRESS_ID => $customer->getDefaultBillingAddressId(),
                SalesChannelContextService::SHIPPING_ADDRESS_ID => $customer->getDefaultShippingAddressId(),
            ]
        );
    }
}
