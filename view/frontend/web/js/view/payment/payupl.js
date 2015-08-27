/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'orba_payupl',
                component: 'Orba_Payupl/js/view/payment/method-renderer/payupl-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);