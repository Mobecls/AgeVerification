<?php

namespace Magenmagic\AgeVerification\Model\EVSRequest;

use Magento\Quote\Model\Quote;

class QuoteRequest implements AbstractFactoryInterface
{
    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var string
     */
    private $dobValue;

    /**
     * @param Quote  $quote
     * @param string $dobValue
     */
    public function __construct(Quote $quote, $dobValue)
    {
        $this->quote    = $quote;
        $this->dobValue = $dobValue;
    }

    /**
     * @return RequestObject
     */
    public function createRequest()
    {
        $billingAddress = $this->quote->getBillingAddress();
        $object         = new RequestObject();

        return $object->setEmail($this->quote->getCustomerEmail())
            ->setFirstName($billingAddress->getFirstname())
            ->setMiddleName($billingAddress->getMiddlename())
            ->setLastName($billingAddress->getLastname())
            ->setState($billingAddress->getRegionCode())
            ->setCountry($billingAddress->getCountryId())
            ->setCity($billingAddress->getCity())
            ->setStreet($billingAddress->getStreetFull())
            ->setZipCode($billingAddress->getPostcode())
            ->setDateOfBirth($this->dobValue);
    }
}
