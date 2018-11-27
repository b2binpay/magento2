define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Customer/js/customer-data',
        'mage/url'
    ],
    function ($, Component, errorProcessor, fullScreenLoader, customerData, url) {
        'use strict';

        return Component.extend({
            redirectAfterPlaceOrder: false,

            defaults: {
                template: 'B2Binpay_Payment/payment/form',
                currencyCode: Object.keys(window.checkoutConfig.payment.b2binpay.currencyCodes)[0]
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'currencyCode'
                    ]);
                return this;
            },

            getCode: function () {
                return 'b2binpay';
            },

            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'wallet': this.currencyCode()
                    }
                };
            },

            afterPlaceOrder: function () {
                var redirect_controller_url = url.build('b2binpay/redirect/index');

                $.post(redirect_controller_url, 'json')
                    .done(function (response) {
                        customerData.invalidate(['cart']);
                        $.mage.redirect(response.url);
                    })
                    .fail(function (response) {
                        errorProcessor.process(response, this.messageContainer);
                    })
                    .always(function () {
                        fullScreenLoader.stopLoader();
                    });
            },

            getCurrencyCodes: function () {
                return _.map(window.checkoutConfig.payment.b2binpay.currencyCodes, function (value, key) {
                    return {
                        'value': key,
                        'currency': value
                    }
                });
            }
        });
    }
);
