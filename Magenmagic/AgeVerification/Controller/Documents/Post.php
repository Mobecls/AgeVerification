<?php

namespace Magenmagic\AgeVerification\Controller\Documents;

use Magenmagic\AgeVerification\Helper\Data;
use Magenmagic\AgeVerification\Model\Upload\CustomerStrategy;
use Magenmagic\AgeVerification\Model\Upload\OrderStrategy;
use Magenmagic\AgeVerification\Model\Upload\StrategyInterface;
use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Action\Action;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session;

/**
 * @category   Magenmagic
 * @package    Magenmagic_AgeVerification
 * @author     Alex Brynov
 */
class Post extends Action
{
    /**
     * Show Contact Us page
     *
     * @return void
     */
    protected $objectManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var UploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var ConfigInterface
     */
    private $contactsConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var StrategyInterface
     */
    private $strategy;

    /**
     * Post constructor.
     *
     * @param Context                $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface  $storeManager
     * @param Filesystem             $filesystem
     * @param UrlInterface           $url
     * @param UploaderFactory        $fileUploaderFactory
     * @param StateInterface         $inlineTranslation
     * @param Escaper                $escaper
     * @param ScopeConfigInterface   $scopeConfig
     * @param Data                   $helper
     * @param TransportBuilder       $transportBuilder
     * @param ConfigInterface        $contactsConfig
     * @param LoggerInterface        $logger
     * @param Session                $customerSession
     * @param OrderStrategy          $orderStrategy
     * @param CustomerStrategy       $customerStrategy
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        UrlInterface $url,
        UploaderFactory $fileUploaderFactory,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        ScopeConfigInterface $scopeConfig,
        Data $helper,
        TransportBuilder $transportBuilder,
        ConfigInterface $contactsConfig,
        LoggerInterface $logger,
        Session $customerSession,
        OrderStrategy $orderStrategy,
        CustomerStrategy $customerStrategy
    ) {
        $this->objectManager       = $objectManager;
        $this->storeManager        = $storeManager;
        $this->filesystem          = $filesystem;
        $this->fileUploaderFactory = $fileUploaderFactory;
        parent::__construct($context);

        $this->url               = $url;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper           = $escaper;
        $this->scopeConfig       = $scopeConfig;
        $this->helper            = $helper;
        $this->transportBuilder  = $transportBuilder;
        $this->contactsConfig    = $contactsConfig;
        $this->logger            = $logger;
        $this->session           = $customerSession;

        $this->strategy = $this->getRequest()->getParam('verification_id')
            ? $orderStrategy
            : $customerStrategy;
    }

    /**
     * Post handler
     */
    public function execute()
    {
        if (!$this->strategy->validate()) {
            $this->messageManager->addErrorMessage(
                __('Wrong Request')
            );

            $this->_redirect($this->_redirect->getRefererUrl());

            return;
        }

        $mediaDir  = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $mediapath = rtrim($mediaDir, '/');
        $uploader  = $this->fileUploaderFactory->create(['fileId' => 'document']);
        $ext       = ['jpg', 'jpeg', 'gif', 'png', 'pdf'];
        $uploader->setAllowedExtensions()
            ->setAllowRenameFiles(true);

        $localPath = 'age_verification/';
        $path      = $mediapath . '/' . $localPath;

        $fileName = $this->strategy->getFileName();

        if (!$uploader->save($path, $fileName . '.' . $uploader->getFileExtension())) {
            $this->messageManager->addErrorMessage(
                __(
                    'There was an error while submitting the documents. Please check file type and try to '
                    . 'resubmit (%s are allowed)',
                    implode(', ', $ext)
                )
            );

            $this->_redirect($this->_redirect->getRefererUrl());

            return;
        }

        $documentUrl = $this->url->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA])
            . $localPath
            . $uploader->getUploadedFileName();

        $this->strategy->postUpload($uploader->getUploadedFileName());

        if ($this->sendEmail($documentUrl)) {
            $this->_redirect('customer/account');
            $this->messageManager->addSuccessMessage(
                __($this->strategy->getSuccessMessage())
            );
        } else {
            $this->messageManager->addErrorMessage(
                __(
                    'There was an error while submitting the documents. Please try to resubmit'
                )
            );

            $this->_redirect($this->_redirect->getRefererUrl());
        }
    }

    /**
     * @param string $documentUrl
     *
     * @return bool
     */
    protected function sendEmail($documentUrl)
    {
        if (!($customerId = $this->session->getId())) {
            return false;
        }

        $this->inlineTranslation->suspend();
        try {
            $postObject = new DataObject();
            $postObject->setData(
                [
                    'customer_id'  => $customerId,
                    'document_url' => $documentUrl,
                ]
            );

            $this->strategy->prepareEmailParams($postObject);

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->strategy->getEmailTemplate())
                ->setTemplateOptions(
                    [
                        'area'  => Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($this->contactsConfig->emailSender())
                ->addTo($this->helper->getRecipientEmail())
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();

        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->inlineTranslation->resume();
            $this->messageManager->addErrorMessage(
                __('We can\'t process your request right now. Please try again')
            );

            return false;
        }

        return true;
    }
}
