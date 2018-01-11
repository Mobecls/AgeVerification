<?php

namespace Magenmagic\AgeVerification\Block\Checkout;

use Magenmagic\AgeVerification\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;

class VerificationLink extends Template
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * VerificationLink constructor.
     *
     * @param Template\Context $context
     * @param Data             $helper
     * @param Session          $checkoutSession
     * @param array            $data
     */
    public function __construct(
        Template\Context $context,
        Data $helper,
        Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper          = $helper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $link  = $this->helper->getAgeVerificationUrlByOrder($order);
        $this->setLink($link);
        $this->setIsGuest($order->getCustomerIsGuest());

        return $link ? parent::_toHtml() : '';
    }
}