<?php

namespace Magenmagic\AgeVerification\Model\EVSRequest;

use Magento\Customer\Model\Data\Customer;

class CustomerRequest implements AbstractFactoryInterface
{
    /**
     * @var Customer
     */
    private $customer;

    /**
     * CustomerRequest constructor.
     *
     * @param Customer $customer
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return RequestObject
     */
    public function createRequest()
    {
        $customer = $this->customer;

        if (!($addresses = $customer->getAddresses())) {
            return null;
        }
        /** @var RequestObject $object */
        $object = new RequestObject();

        return $object->setEmail($customer->getEmail())
            ->setFirstName($customer->getFirstname())
            ->setLastName($customer->getLastname())
            ->setMiddleName($customer->getMiddlename())
            ->setCountry($addresses[0]->getCountryId())
            ->setCity($addresses[0]->getCity())
            ->setZipCode($addresses[0]->getPostcode())
            ->setState($addresses[0]->getRegion()->getRegion())
            ->setStreet(implode(' ', $addresses[0]->getStreet()))
            //->setDateOfBirth($customer->getDob());
            ->setDateOfBirth('1992-07-25');
    }
}
