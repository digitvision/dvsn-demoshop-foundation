/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

import template from './sw-dashboard-index.html.twig';
import './sw-dashboard-index.scss';

const { Component } = Shopware;

Component.override('sw-dashboard-index', {
    template,
});
