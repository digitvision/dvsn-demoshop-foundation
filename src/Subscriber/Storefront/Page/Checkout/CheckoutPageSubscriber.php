<?php declare(strict_types=1);

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

namespace Dvsn\DemoshopFoundation\Subscriber\Storefront\Page\Checkout;

use Shopware\Core\Framework\Adapter\Translation\AbstractTranslator;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Finish\CheckoutFinishPageLoadedEvent;
use Shopware\Storefront\Page\PageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutPageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AbstractTranslator $translator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => 'onPageLoaded',
            CheckoutFinishPageLoadedEvent::class => 'onPageLoaded',
        ];
    }

    public function onPageLoaded(PageLoadedEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->hasSession(true)) {
            return;
        }

        $session = $request->getSession();

        $session->getFlashBag()->add(
            'danger',
            $this->translator->trans('dvsn-demoshop-foundation.home.demoshop-alert', [])
        );
    }
}
