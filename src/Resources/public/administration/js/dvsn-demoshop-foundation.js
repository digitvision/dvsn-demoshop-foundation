(function(){var e={932:function(){},940:function(e,t,n){var r=n(932);r.__esModule&&(r=r.default),"string"==typeof r&&(r=[[e.id,r,""]]),r.locals&&(e.exports=r.locals),n(346).Z("eccf3774",r,!0,{})},346:function(e,t,n){"use strict";function r(e,t){for(var n=[],r={},s=0;s<t.length;s++){var o=t[s],i=o[0],a={id:e+":"+s,css:o[1],media:o[2],sourceMap:o[3]};r[i]?r[i].parts.push(a):n.push(r[i]={id:i,parts:[a]})}return n}n.d(t,{Z:function(){return m}});var s="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!s)throw Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var o={},i=s&&(document.head||document.getElementsByTagName("head")[0]),a=null,d=0,l=!1,u=function(){},c=null,p="data-vue-ssr-id",f="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function m(e,t,n,s){l=n,c=s||{};var i=r(e,t);return h(i),function(t){for(var n=[],s=0;s<i.length;s++){var a=o[i[s].id];a.refs--,n.push(a)}t?h(i=r(e,t)):i=[];for(var s=0;s<n.length;s++){var a=n[s];if(0===a.refs){for(var d=0;d<a.parts.length;d++)a.parts[d]();delete o[a.id]}}}}function h(e){for(var t=0;t<e.length;t++){var n=e[t],r=o[n.id];if(r){r.refs++;for(var s=0;s<r.parts.length;s++)r.parts[s](n.parts[s]);for(;s<n.parts.length;s++)r.parts.push(_(n.parts[s]));r.parts.length>n.parts.length&&(r.parts.length=n.parts.length)}else{for(var i=[],s=0;s<n.parts.length;s++)i.push(_(n.parts[s]));o[n.id]={id:n.id,refs:1,parts:i}}}}function v(){var e=document.createElement("style");return e.type="text/css",i.appendChild(e),e}function _(e){var t,n,r=document.querySelector("style["+p+'~="'+e.id+'"]');if(r){if(l)return u;r.parentNode.removeChild(r)}if(f){var s=d++;t=g.bind(null,r=a||(a=v()),s,!1),n=g.bind(null,r,s,!0)}else t=w.bind(null,r=v()),n=function(){r.parentNode.removeChild(r)};return t(e),function(r){r?(r.css!==e.css||r.media!==e.media||r.sourceMap!==e.sourceMap)&&t(e=r):n()}}var b=function(){var e=[];return function(t,n){return e[t]=n,e.filter(Boolean).join("\n")}}();function g(e,t,n,r){var s=n?"":r.css;if(e.styleSheet)e.styleSheet.cssText=b(t,s);else{var o=document.createTextNode(s),i=e.childNodes;i[t]&&e.removeChild(i[t]),i.length?e.insertBefore(o,i[t]):e.appendChild(o)}}function w(e,t){var n=t.css,r=t.media,s=t.sourceMap;if(r&&e.setAttribute("media",r),c.ssrId&&e.setAttribute(p,t.id),s&&(n+="\n/*# sourceURL="+s.sources[0]+" */\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(s))))+" */"),e.styleSheet)e.styleSheet.cssText=n;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(n))}}}},t={};function n(r){var s=t[r];if(void 0!==s)return s.exports;var o=t[r]={id:r,exports:{}};return e[r](o,o.exports,n),o.exports}n.d=function(e,t){for(var r in t)n.o(t,r)&&!n.o(e,r)&&Object.defineProperty(e,r,{enumerable:!0,get:t[r]})},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="bundles/dvsndemoshopfoundation/",window?.__sw__?.assetPath&&(n.p=window.__sw__.assetPath+"/bundles/dvsndemoshopfoundation/"),function(){"use strict";n(940);/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */let{Component:e}=Shopware;e.override("sw-dashboard-index",{template:""});/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */let{Component:t}=Shopware;t.override("sw-extension-card-base",{template:"",computed:{allowDisable(){return!1},isRemovable(){return!1},isUninstallable(){return!1},isUpdateable(){return!1}}});/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */let{Component:r}=Shopware;r.override("sw-extension-my-extensions-index",{template:"\n{% block sw_extension_my_extensions_index_smart_bar_actions_file_upload %}{% endblock %}\n{% block sw_extension_my_extensions_index_smart_bar_tabs_theme %}{% endblock %}\n{% block sw_extension_my_extensions_index_smart_bar_tabs_recommendation %}{% endblock %}\n{% block sw_extension_my_extensions_index_smart_bar_tabs_account %}{% endblock %}\n"});/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */let{Component:s}=Shopware;s.override("sw-extension-store-landing-page",{template:"",methods:{activateStore(){}}});/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */let{Component:o}=Shopware;o.override("sw-settings-item",{template:"",computed:{classes(){return["sw.settings.usage.data.index","sw.first.run.wizard.index","sw.integration.index","sw.settings.mailer.index","sw.settings.store.index","sw.settings.shopware.updates.wizard"].includes(this.to.name)?{"is--disabled":!0}:{"is--disabled":this.disabled}}}});/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnDemoshopFoundation
 * @copyright (c) 2024 digitvision
 */}()})();