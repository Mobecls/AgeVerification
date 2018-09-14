<?php

namespace Magenmagic\AgeVerification\Observer\Customer;

use Magenmagic\AgeVerification\Helper\Data;
use Magenmagic\AgeVerification\Model\EVSRequest;
use Magenmagic\AgeVerification\Model\EVSRequest\CustomerRequestFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @category   Magenmagic
 * @package    Magenmagic_AgeVerification
 * @author     Alex Brynov <lexpochta@gmail.com>
 */
class RegistrationSuccess implements ObserverInterface
{
    /**
     * @var EVSRequest
     */
    private $evsRequest;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var CustomerRequestFactory
     */
    private $customerRequestFactory;

    /**
     * RegistrationSuccess constructor.
     *
     * @param EVSRequest                  $evsRequest
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param Data                        $helper
     * @param CustomerRequestFactory      $customerRequestFactory
     */
    public function __construct(
        EVSRequest $evsRequest,
        CustomerRepositoryInterface $customerRepositoryInterface,
        Data $helper,
        CustomerRequestFactory $customerRequestFactory
    ) {

        $this->evsRequest             = $evsRequest;
        $this->customerRepository     = $customerRepositoryInterface;
        $this->helper                 = $helper;
        $this->customerRequestFactory = $customerRequestFactory;
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
        $customer = $observer->getCustomer();

        $request = $this->customerRequestFactory->create(['customer' => $customer])
            ->createRequest();

        $customer->setCustomAttribute(
            Data::ATTRIBUTE_CODE_VERIFIED,
            (bool)($verificationId = $this->evsRequest->validate($request))
        );
        $customer->setCustomAttribute(Data::ATTRIBUTE_CODE_ID, $verificationId);
        $this->customerRepository->save($customer);
    }
}
