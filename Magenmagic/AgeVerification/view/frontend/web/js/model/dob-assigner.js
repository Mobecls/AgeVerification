/*global alert*/
define([
    'jquery'
], function ($) {
    'use strict';

    return function (quote, isBilling) {

        var parent,
            shippingAddress = quote.shippingAddress(),
            billingAddress  = quote.billingAddress();

        if (isBilling) {
            // skip if shipping address is used
            if (shippingAddress && billingAddress && shippingAddress.getCacheKey() === billingAddress.getCacheKey()) {
                return;
            }

            parent = $('.payment-method._active');
        } else {
            // skip if billing address is used
            if (shippingAddress && billingAddress && shippingAddress.getCacheKey() !== billingAddress.getCacheKey()) {
                return;
            }

            parent = $('#checkoutSteps #shipping');
        }

        var input = parent.find('input[name="custom_attributes[mm_dob]"]');

        if (input.length) {
            $.each(['billingAddress', 'shippingAddress'], function (i, key) {
                var address = quote[key]()

                if (address) {
                    if (address['extension_attributes'] === undefined) {
                        address['extension_attributes'] = {};
                    }

                    address['extension_attributes']['mm_dob'] = input.val();

                    return false;
                }
            })
        }
    };
});
