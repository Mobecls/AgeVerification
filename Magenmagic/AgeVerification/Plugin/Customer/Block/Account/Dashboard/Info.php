<?php

namespace Magenmagic\AgeVerification\Plugin\Customer\Block\Account\Dashboard;

use Magenmagic\AgeVerification\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;

class Info
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
     * @param Session                     $customerSession
     * @param CustomerRepositoryInterface $repository
     * @param Data                        $helper
     */
    public function __construct(
        Session $customerSession,
        CustomerRepositoryInterface $repository,
        Data $helper
    ) {
        $this->customerSession = $customerSession;
        $this->repository      = $repository;
        $this->helper          = $helper;
    }

    /**
     * @param \Magento\Customer\Block\Account\Dashboard\Info $interceptor
     * @param string                                         $name
     *
     * @return string
     */
    public function afterGetName(\Magento\Customer\Block\Account\Dashboard\Info $interceptor, $name)
    {
        return $name . ' (' . $this->getTag() . ')';
    }

    /**
     * @return string
     */
    private function getTag()
    {
        $value = $this->repository->getById($this->customerSession->getId())->getCustomAttribute(
            Data::ATTRIBUTE_CODE_VERIFIED
        )->getValue();

        return $value ? $this->helper->getTagVerified() : $this->helper->getTagNotVerified();
    }
}
