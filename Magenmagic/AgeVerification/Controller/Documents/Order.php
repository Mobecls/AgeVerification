<?php

namespace Magenmagic\AgeVerification\Controller\Documents;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

/**
 * @category   Magenmagic
 * @package    Magenmagic_AgeVerification
 * @author     Alex Brynov
 */
class Order extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param Context            $context
     * @param PageFactory        $resultPageFactory
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->encryptor         = $encryptor;
    }

    /**
     * Customer register form page
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Order Age Verification'));

        $resultPage->getLayout()
            ->getBlock('document.form')
            ->setData('verification_id', $this->getRequest()->getParam('verification_id'));

        return $resultPage;
    }


}
