/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

import template from './sw-settings-mailer.html.twig';

const { Component } = Shopware;

Component.override('sw-settings-mailer', {
    template,
});
