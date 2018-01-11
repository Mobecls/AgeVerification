<?php

namespace Magenmagic\AgeVerification\Observer\Customer;

use Magenmagic\AgeVerification\Helper\Data;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\Session;

/**
 * @category   Magenmagic
 * @package    Magenmagic_AgeVerification
 * @author     Alex Brynov <lexpochta@gmail.com>
 */
class Login implements ObserverInterface
{
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ManagerInterface
     */
    private $messageContainer;

    /**
     * Login constructor.
     *
     * @param UrlInterface     $url
     * @param Session          $session
     * @param Data             $helper
     * @param ManagerInterface $messageContainer
     */
    public function __construct(
        UrlInterface $url,
        Session $session,
        Data $helper,
        ManagerInterface $messageContainer
    ) {
        $this->url              = $url;
        $this->session          = $session;
        $this->helper           = $helper;
        $this->messageContainer = $messageContainer;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isEnabled()) {
            return;
        }

        /** @var CustomerInterface $customer */
        $customer = $observer->getEvent()->getCustomer();

        $redirectUrl = null;
        $isVerified  = $customer->getData(Data::ATTRIBUTE_CODE_VERIFIED);

        // Customer did not submit verification request, redirect him to page with verification form
        if ($isVerified === null) {
            $redirectUrl = 'magenmagic_ageverification';
        } elseif (!$isVerified) {
            // Customer already submitted documents
            if ($customer->getData(Data::ATTRIBUTE_CODE_DOCUMENT_LINK)) {
                $this->messageContainer->addWarningMessage(__('Age verification documents are currently pending approval'));

                return;
            }

            $redirectUrl = 'magenmagic_ageverification/documents';
        }

        if ($redirectUrl) {
            $this->session->setBeforeAuthUrl($this->url->getUrl($redirectUrl));
        }
    }
}
