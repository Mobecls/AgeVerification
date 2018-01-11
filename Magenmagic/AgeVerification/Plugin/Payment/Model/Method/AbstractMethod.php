<?php

namespace Magenmagic\AgeVerification\Plugin\Payment\Model\Method;

use Magenmagic\AgeVerification\Plugin\Payment\Method\PaymentConfigAbstract;
use Magento\Payment\Model\Method\AbstractMethod as OriginalAbstractMethod;

class AbstractMethod extends PaymentConfigAbstract
{
    /**
     * Retrieve information from payment configuration
     *
     * @param OriginalAbstractMethod                     $interceptor
     * @param callable                                   $proceed
     * @param string                                     $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     * @deprecated 100.2.0
     */
    public function aroundGetConfigData(OriginalAbstractMethod $interceptor, callable $proceed, $field, $storeId = null)
    {
        $originalAction = $proceed($field, $storeId);
        // we need only "payment_action" action
        if ($field !== 'payment_action') {
            return $originalAction;
        }

        return $this->getModifiedPaymentAction($originalAction);
    }
}