<?php

namespace Magenmagic\AgeVerification\Plugin\Payment\Method;

use Magento\Payment\Model\Method\AbstractMethod;
use Magenmagic\AgeVerification\Helper\Data;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;

abstract class PaymentConfigAbstract
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    private $repository;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @param Session                     $customerSession
     * @param CustomerRepositoryInterface $repository
     * @param Data                        $helper
     * @param CheckoutSession             $checkoutSession
     */
    public function __construct(
        Session $customerSession,
        CustomerRepositoryInterface $repository,
        Data $helper,
        CheckoutSession $checkoutSession
    ) {
        $this->customerSession = $customerSession;
        $this->repository      = $repository;
        $this->helper          = $helper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param $originalAction
     *
     * @return string
     */
    protected function getModifiedPaymentAction($originalAction)
    {
        if (AbstractMethod::ACTION_AUTHORIZE === $originalAction // skip if default method is already just "authorize"
            || !$this->helper->isEnabled()
        ) {
            return $originalAction;
        }

        $key   = Data::ATTRIBUTE_CODE_VERIFIED;
        $quote = $this->checkoutSession->getQuote();
        if ($quote->hasData($key)) {
            // Guest checkout. Age verified from quote
            if (!$quote->getData($key)) {
                return AbstractMethod::ACTION_AUTHORIZE;
            }
        } elseif ($id = $this->customerSession->getId()) {
            // Customer checkout. Age verified from customer
            $customer = $this->repository->getById($id);

            $verifiedAttribute = $customer->getCustomAttribute('mm_age_is_verified');
            $verified          = $verifiedAttribute ? $verifiedAttribute->getValue() : false;
            if (!$verified) {
                return AbstractMethod::ACTION_AUTHORIZE;
            }
        }

        return $originalAction;
    }
}


