/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */

/* eslint-disable no-unused-vars */
/* eslint-disable no-unreachable */
/* eslint-disable no-empty */
/* eslint-disable no-useless-escape */

export default class DvsnDemoshopFoundationCheckoutRegister extends window.PluginBaseClass {
    init() {
        if (this.el.querySelectorAll('.login-collapse-toggle').length < 1) {
            return;
        }

        this.el.querySelector('.login-collapse-toggle').click();
    }
}
