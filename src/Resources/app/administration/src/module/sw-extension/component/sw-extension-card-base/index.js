/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

import template from './sw-extension-card-base.html.twig';

const { Component } = Shopware;

Component.override('sw-extension-card-base', {
    template,

    computed: {
        allowDisable() {
            return false;
        },

        isRemovable() {
            return false;
        },

        isUninstallable() {
            return false;
        },

        isUpdateable() {
            return false;
        },
    }
});
