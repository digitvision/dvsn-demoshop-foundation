/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

import template from './sw-settings-item.html.twig';

const { Component } = Shopware;

Component.override('sw-settings-item', {
    template,

    computed: {
        classes() {
            const disabled = [
                'sw.settings.usage.data.index',
                'sw.first.run.wizard.index',
                'sw.integration.index',
                'sw.settings.mailer.index',
                'sw.settings.store.index',
                'sw.settings.shopware.updates.wizard'
            ];

            if (disabled.includes(this.to.name)) {
                return {
                    'is--disabled': true,
                };
            }

            return {
                'is--disabled': this.disabled,
            };
        },
    },
});
