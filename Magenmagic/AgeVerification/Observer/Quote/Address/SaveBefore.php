<?php

namespace Magenmagic\AgeVerification\Observer\Quote\Address;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime;

/**
 * @category   Magenmagic
 * @package    Magenmagic_AgeVerification
 * @author     Alex Brynov <lexpochta@gmail.com>
 */
class SaveBefore implements ObserverInterface
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * SaveBefore constructor.
     *
     * @param DateTime $dateTime
     */
    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quoteAddress */
        $quoteAddress = $observer->getQuoteAddress();
        if (($attributes = $quoteAddress->getData('extension_attributes')) && $date = $attributes->getMmDob()) {
            $quoteAddress->setData('mm_dob', $this->dateTime->formatDate($date, $this->dateTime::DATE_INTERNAL_FORMAT));
        }
    }
}
