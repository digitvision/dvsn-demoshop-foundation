/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

const { Component } = Shopware;

Component.override('sw-login-login', {
    data() {
        return {
            username: 'admin',
            password: 'shopware'
        };
    },
});
