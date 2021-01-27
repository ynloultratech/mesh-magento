define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/select-shipping-address',
        'mage/url',
        'https://egiftcert-widget.paynup.com/index.js'
    ],
    function ($, Component, quote, customer, checkoutData, selectShippingAddressAction, url) {
        'use strict';
        return Component.extend({

            defaults: {
                template: 'Mesh_MeshPayment/payment/meshpayment',
                redirectAfterPlaceOrder: false,
            },

            initialize: function () {
                this._super();

            },

            isActive: function () {
                return true;
            },

            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }

                var self = this;
                this.isPlaceOrderActionAllowed(false);

                self.realPlaceOrder();
                this.isPlaceOrderActionAllowed(true);
            },

            realPlaceOrder: function () {
                var self = this;
                this.getPlaceOrderDeferredObject()
                    .fail(
                        function () {
                            self.isPlaceOrderActionAllowed(true);
                        }
                    ).done(
                    function () {
                        self.afterPlaceOrder();

                        if (self.redirectAfterPlaceOrder) {
                            redirectOnSuccessAction.execute();
                        }
                    }
                );
            },

            afterPlaceOrder: function () {
                // $.mage.redirect('/meshpayment/paynup/paynup');
                window.location.replace(url.build('/meshpayment/paynup/paynup'));
                return false;
            },
        });
    }
);
