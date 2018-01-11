<?php

namespace Magenmagic\AgeVerification\Plugin\Payment\Method;

use Magento\Payment\Model\Method\Adapter as OriginalAdapter;

class Adapter extends PaymentConfigAbstract
{
    /**
     * @param OriginalAdapter $interceptor
     * @param callable        $proceed
     *
     * @return int
     */
    public function aroundGetConfigPaymentAction(OriginalAdapter $interceptor, callable $proceed)
    {
        return $this->getModifiedPaymentAction($proceed());
    }
}


