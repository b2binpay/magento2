define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';

        rendererList.push(
            {
                type: 'b2binpay',
                component: 'B2Binpay_Payment/js/view/payment/method-renderer/b2binpay'
            }
        );

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
