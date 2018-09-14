<?php

namespace Magenmagic\AgeVerification\Model\Upload;

use Magenmagic\AgeVerification\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderStrategy implements StrategyInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * OrderStrategy constructor.
     *
     * @param RequestInterface            $request
     * @param OrderRepositoryInterface    $orderRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param EncryptorInterface          $encryptor
     */
    public function __construct(
        RequestInterface $request,
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository,
        EncryptorInterface $encryptor
    ) {
        $this->request            = $request;
        $this->orderRepository    = $orderRepository;
        $this->encryptor          = $encryptor;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return string
     */
    public function getSuccessMessage()
    {
        return 'Your documents have been successfully uploaded.'
            . ' We will review it shortly and send you notification about the order';
    }

    /**
     * @return bool
     */
    public function validate()
    {
        $orderId        = $this->encryptor->decrypt($this->request->getParam('verification_id'));
        $orderInterface = $this->orderRepository;

        try {
            $this->order = $orderInterface->get($orderId);
        } catch (NoSuchEntityException $e) {
            return false;
        }


        return (bool)$this->order->getEntityId();
    }

    /**
     * @param DataObject $object
     *
     * @return void
     */
    public function prepareEmailParams(DataObject $object)
    {
        $object->setData('increment_id', $this->order->getIncrementId());
    }

    /**
     * @return string
     */
    public function getEmailTemplate()
    {
        return Data::EMAIL_TEMPLATE_VERIFICATION_ORDER_REQUEST;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return md5($this->encryptor->encrypt($this->order->getIncrementId()));
    }

    /**
     * @param string $fileName
     *
     * @return void
     */
    public function postUpload($fileName)
    {
        $this->order->setMmAgeVerificationDoc($fileName);
        $this->orderRepository->save($this->order);

        if (!$this->order->getCustomerIsGuest()) {
            $customer = $this->customerRepository->getById($this->order->getCustomerId());
            $customer->setCustomAttribute(Data::ATTRIBUTE_CODE_DOCUMENT_LINK, $fileName);
            $this->customerRepository->save($customer);
        } else {
            //conditionally create customer
            // send welcome email with password
        }
    }
}