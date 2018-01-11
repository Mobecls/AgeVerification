define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magenmagic_AgeVerification/js/model/dob-assigner'
], function ($, wrapper, quote, dobAssigner) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction, messageContainer) {

            dobAssigner(quote, false)

            return originalAction(setShippingInformationAction);
        });
    };
});