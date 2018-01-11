/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    config : {
        mixins : {
            'Magento_Checkout/js/action/set-shipping-information' : {
                'Magenmagic_AgeVerification/js/action/set-shipping-information-mixin' : true
            },
            'Magento_Checkout/js/action/set-billing-address'      : {
                'Magenmagic_AgeVerification/js/action/set-billing-address-mixin' : true
            },
            'Magento_Checkout/js/action/place-order'              : {
                'Magenmagic_AgeVerification/js/action/place-order-mixin' : true
            }
        }
    }
};
