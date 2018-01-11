<?php

namespace Magenmagic\AgeVerification\Model\Upload;

use Magenmagic\AgeVerification\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;

class CustomerStrategy implements StrategyInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * OrderStrategy constructor.
     *
     * @param RequestInterface            $request
     * @param Session                     $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        RequestInterface $request,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->request            = $request;
        $this->customerSession    = $customerSession;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return string
     */
    public function getSuccessMessage()
    {
        return 'Your documents have been successfully uploaded. We will review it shortly';
    }

    /**
     * @return string
     */
    public function getEmailTemplate()
    {
        return Data::EMAIL_TEMPLATE_VERIFICATION_REQUEST;
    }

    /**
     * @param DataObject $object
     *
     * @return void
     */
    public function prepareEmailParams(DataObject $object)
    {

    }

    /**
     * @return bool
     */
    public function validate()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return md5(time() . uniqid());
    }

    /**
     * @param string $fileName
     *
     * @return void
     */
    public function postUpload($fileName)
    {
        $customer = $this->customerRepository->getById($this->customerSession->getId());
        $customer->setCustomAttribute(Data::ATTRIBUTE_CODE_DOCUMENT_LINK, $fileName);
        $this->customerRepository->save($customer);
    }
}