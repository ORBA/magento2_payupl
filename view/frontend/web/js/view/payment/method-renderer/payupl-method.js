/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/action/place-order'
    ],
    function ($, Component, customer, placeOrderAction) {
        'use strict';

        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'Orba_Payupl/payment/payupl'
            },
            getData: function() {
                return {
                    "method": this.item.method,
                    "additional_data": this.getAdditionalData()
                };
            },
            getAdditionalData: function() {
                var paytypeRadio = $("input[name=payment\\[additional_data\\]\\[paytype\\]]:checked");
                if (paytypeRadio.length) {
                    return {
                        "paytype": paytypeRadio.val()
                    }
                }
                return null;
            },
            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }
                var self = this,
                    placeOrder,
                    emailValidationResult = customer.isLoggedIn(),
                    loginFormSelector = 'form[data-role=email-with-possible-login]';
                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }
                if (emailValidationResult && this.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData(), this.redirectAfterPlaceOrder);
                    $.when(placeOrder).done(function () {
                        $.mage.redirect(window.checkoutConfig.payment.orbaPayupl.redirectUrl);
                    }).fail(function(){
                        self.isPlaceOrderActionAllowed(true);
                    });
                    return true;
                }
                return false;
            },
            getPaytypes: function() {
                return window.checkoutConfig.payment.orbaPayupl.paytypes;
            }
        });
    }
);