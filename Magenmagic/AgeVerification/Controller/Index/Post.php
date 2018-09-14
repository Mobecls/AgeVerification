<?php

namespace Magenmagic\AgeVerification\Controller\Index;

use Magenmagic\AgeVerification\Helper\Data;
use Magenmagic\AgeVerification\Model\EVSRequest;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magenmagic\AgeVerification\Model\EVSRequest\CustomerRequestFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @category   Magenmagic
 * @package    Magenmagic_AgeVerification
 * @author     Alex Brynov
 */
class Post extends AbstractAccount
{
    /**
     * Form code for data extractor
     */
    const FORM_DATA_EXTRACTOR_CODE = 'customer_account_edit';

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var CustomerExtractor
     */
    private $customerExtractor;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var EVSRequest
     */
    private $evsRequest;

    /**
     * @var Mapper
     */
    protected $customerMapper;

    /**
     * @var CustomerRequestFactory
     */
    private $customerRequestFactory;

    /**
     * @param Context                     $context
     * @param Validator                   $validator
     * @param CustomerExtractor           $customerExtractor
     * @param Session                     $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param EVSRequest                  $evsRequest
     * @param CustomerRequestFactory      $customerRequestFactory
     */
    public function __construct(
        Context $context,
        Validator $validator,
        CustomerExtractor $customerExtractor,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        EVSRequest $evsRequest,
        CustomerRequestFactory $customerRequestFactory
    ) {
        parent::__construct($context);
        $this->formKeyValidator       = $validator;
        $this->customerExtractor      = $customerExtractor;
        $this->session                = $customerSession;
        $this->customerRepository     = $customerRepository;
        $this->context                = $context;
        $this->evsRequest             = $evsRequest;
        $this->customerRequestFactory = $customerRequestFactory;
    }

    /**
     * Change customer email or password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $validFormKey   = $this->formKeyValidator->validate($this->getRequest());

        if ($validFormKey && $this->getRequest()->isPost()) {
            $currentCustomerDataObject   = $this->getCustomerDataObject($this->session->getCustomerId());
            $customerCandidateDataObject = $this->populateNewCustomerDataObject(
                $this->_request,
                $currentCustomerDataObject
            );

            try {
                $request = $this->customerRequestFactory->create(['customer' => $customerCandidateDataObject])
                    ->createRequest();

                $validationResult = $this->evsRequest->validate($request);
                $currentCustomerDataObject->setCustomAttribute(
                    Data::ATTRIBUTE_CODE_VERIFIED,
                    (bool)$validationResult
                );
                $currentCustomerDataObject->setCustomAttribute(
                    Data::ATTRIBUTE_CODE_ID,
                    $validationResult
                );
                $this->customerRepository->save($currentCustomerDataObject);

                $path = 'magenmagic_ageverification/documents';
                if ($validationResult) {
                    $this->messageManager->addSuccessMessage(__('Your age was successfully verified.'));
                    $path = 'customer/account';
                }

                return $resultRedirect->setPath($path);
            } catch (InputException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                foreach ($e->getErrors() as $error) {
                    $this->messageManager->addErrorMessage($error->getMessage());
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t process the request.'));
            }

            $this->session->setCustomerFormData($this->getRequest()->getPostValue());
        }

        return $resultRedirect->setPath('*/*');
    }

    /**
     * Get customer data object
     *
     * @param int $customerId
     *
     * @return CustomerInterface
     */
    private function getCustomerDataObject($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * Create Data Transfer Object of customer candidate
     *
     * @param RequestInterface  $inputData
     * @param CustomerInterface $currentCustomerData
     *
     * @return CustomerInterface
     */
    private function populateNewCustomerDataObject(
        RequestInterface $inputData,
        CustomerInterface $currentCustomerData
    ) {
        $attributeValues = $this->getCustomerMapper()->toFlatArray($currentCustomerData);
        $customerDto     = $this->customerExtractor->extract(
            self::FORM_DATA_EXTRACTOR_CODE,
            $inputData,
            $attributeValues
        );
        $customerDto->setId($currentCustomerData->getId());
        if (!$customerDto->getAddresses()) {
            $customerDto->setAddresses($currentCustomerData->getAddresses());
        }

        return $customerDto;
    }

    /**
     * Get Customer Mapper instance
     *
     * @return Mapper
     *
     * @deprecated 100.1.3
     */
    private function getCustomerMapper()
    {
        if ($this->customerMapper === null) {
            $this->customerMapper = ObjectManager::getInstance()->get(Mapper::class);
        }

        return $this->customerMapper;
    }
}
