/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

import template from './sw-extension-store-landing-page.html.twig';

const { Component } = Shopware;

Component.override('sw-extension-store-landing-page', {
    template,

    methods: {
        activateStore() {
        },
    },
});
