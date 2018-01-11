<?php

namespace Magenmagic\AgeVerification\Controller\Index;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\DataObject;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

/**
 * @category   Magenmagic
 * @package    Magenmagic_AgeVerification
 * @author     Alex Brynov
 */
class Index extends AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Registration
     */
    protected $registration;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectConverter;

    /**
     * @param Context                   $context
     * @param Session                   $customerSession
     * @param PageFactory               $resultPageFactory
     * @param CustomerRepository        $customerRepository
     * @param SimpleDataObjectConverter $dataObjectConverter
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        CustomerRepository $customerRepository,
        SimpleDataObjectConverter $dataObjectConverter
    ) {
        $this->session           = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
        $this->customerRepository  = $customerRepository;
        $this->dataObjectConverter = $dataObjectConverter;
    }

    /**
     * Customer register form page
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->session->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*');

            return $resultRedirect;
        }

        $customerId         = $this->session->getCustomerId();
        $customerDataObject = $this->customerRepository->getById($customerId);
        $data               = $this->dataObjectConverter->toFlatArray(
            $customerDataObject,
            CustomerInterface::class
        );

        $dataObject = new DataObject();
        $dataObject->setData($data);

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Age Verification'));
        $resultPage->getLayout()->getBlock('customer_form_register')
            ->setFormData($dataObject)
            ->setPostUrl($this->_url->getUrl('age-verification/index/post'));

        return $resultPage;
    }
}