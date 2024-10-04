(function(){"use strict";var e={};e.p="bundles/dvsndemoshopfoundation/",window?.__sw__?.assetPath&&(e.p=window.__sw__.assetPath+"/bundles/dvsndemoshopfoundation/"),function(){/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */let{Component:e}=Shopware;e.override("sw-extension-card-base",{template:"",computed:{allowDisable(){return!1},isRemovable(){return!1},isUninstallable(){return!1},isUpdateable(){return!1}}});/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */let{Component:s}=Shopware;s.override("sw-extension-my-extensions-index",{template:"\n{% block sw_extension_my_extensions_index_smart_bar_actions_file_upload %}{% endblock %}\n{% block sw_extension_my_extensions_index_smart_bar_tabs_theme %}{% endblock %}\n{% block sw_extension_my_extensions_index_smart_bar_tabs_recommendation %}{% endblock %}\n{% block sw_extension_my_extensions_index_smart_bar_tabs_account %}{% endblock %}\n"});/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */let{Component:n}=Shopware;n.override("sw-settings-item",{template:"",computed:{classes(){return["sw.settings.usage.data.index","sw.first.run.wizard.index","sw.integration.index","sw.settings.mailer.index","sw.settings.store.index","sw.settings.shopware.updates.wizard"].includes(this.to.name)?{"is--disabled":!0}:{"is--disabled":this.disabled}}}});/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */}()})();