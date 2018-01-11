define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magenmagic_AgeVerification/js/model/dob-assigner'
], function ($, wrapper, quote, dobAssigner) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, messageContainer) {

            dobAssigner(quote, true)

            return originalAction(messageContainer);
        });
    };
});