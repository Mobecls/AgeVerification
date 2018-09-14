<?php

namespace Magenmagic\AgeVerification\Observer\Sales;

use Magenmagic\AgeVerification\Helper\Data;
use Magenmagic\AgeVerification\Model\EVSRequest;
use Magenmagic\AgeVerification\Model\EVSRequest\QuoteRequestFactory;
use Magenmagic\AgeVerification\Model\EVSRequest\RequestObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;

/**
 * @category   Magenmagic
 * @package    Magenmagic_AgeVerification
 * @author     Alex Brynov <lexpochta@gmail.com>
 */
class QuoteSubmitBefore implements ObserverInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var QuoteRequestFactory
     */
    private $quoteRequestFactory;

    /**
     * @var EVSRequest
     */
    private $evsRequest;

    /**
     * QuoteSubmitBefore constructor.
     *
     * @param Data                $helper
     * @param ManagerInterface    $messageManager
     * @param QuoteRequestFactory $quoteRequestFactory
     * @param EVSRequest          $evsRequest
     */
    public function __construct(
        Data $helper,
        ManagerInterface $messageManager,
        QuoteRequestFactory $quoteRequestFactory,
        EVSRequest $evsRequest
    ) {
        $this->helper              = $helper;
        $this->messageManager      = $messageManager;
        $this->quoteRequestFactory = $quoteRequestFactory;
        $this->evsRequest          = $evsRequest;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getQuote();
        /** @var \Magento\Sales\Model\Order $order */
        $order    = $observer->getOrder();
        $customer = $quote->getCustomer();

        // Do not validate if disabled or logged in
        if (!$this->helper->isEnabled() || $customer->getId()) {
            if ($customer->getId()) {
                $verifiedAttribute   = $customer->getCustomAttribute('mm_age_is_verified');
                $verifiedIdAttribute = $customer->getCustomAttribute('mm_age_verification_id');
                $verified            = $verifiedAttribute ? $verifiedAttribute->getValue() : false;
                $verifiedId          = $verifiedIdAttribute ? $verifiedIdAttribute->getValue() : false;

                if ($ext = $order->getExtensionAttributes()) {
                    $ext->setMmAgeIsVerified($verified);
                    $ext->setMmAgeVerificationId($verifiedId);
                }
                $order->setMmAgeIsVerified($verified);
                $order->setMmAgeVerificationId($verifiedId);
            }

            return;
        }

        $dob = $quote->getBillingAddress()->getMmDob();
        if (!$dob) {
            throw new LocalizedException(__('Please specify Date of Birth'));
        }

        /** @var RequestObject $request */
        $request = $this->quoteRequestFactory
            ->create(['quote' => $quote, 'dobValue' => $dob])
            ->createRequest();

        $verified = $this->evsRequest->validate($request);
        $quote->setData(Data::ATTRIBUTE_CODE_VERIFIED, (bool)$verified);
        $quote->setData(Data::ATTRIBUTE_CODE_ID, $verified);
        $observer->getOrder()->setData(Data::ATTRIBUTE_CODE_VERIFIED, (bool)$verified);
        $observer->getOrder()->setData(Data::ATTRIBUTE_CODE_ID, $verified);

        $order->getExtensionAttributes()->setMmAgeIsVerified((bool)$verified);
        $order->getExtensionAttributes()->setMmAgeVerificationId($verified);
        $order->setMmAgeIsVerified((bool)$verified);
        $order->setMmAgeVerificationId($verified);
    }
}
