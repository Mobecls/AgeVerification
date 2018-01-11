<?php

namespace Magenmagic\AgeVerification\Controller\Documents;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

/**
 * @category   Magenmagic
 * @package    Magenmagic_AgeVerification
 * @author     Alex Brynov
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param Context     $context
     * @param Session     $customerSession
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->session           = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
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
            $resultRedirect->setPath('/');

            return $resultRedirect;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Age Verification'));

        return $resultPage;
    }
}
