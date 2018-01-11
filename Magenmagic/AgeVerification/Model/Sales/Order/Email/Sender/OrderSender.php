<?php

namespace Magenmagic\AgeVerification\Model\Sales\Order\Email\Sender;

use Magenmagic\AgeVerification\Helper\Data;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender\OrderSender as OrderSenderOriginal;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;

class OrderSender extends OrderSenderOriginal
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * OrderSender constructor.
     *
     * @param Template                                           $templateContainer
     * @param OrderIdentity                                      $identityContainer
     * @param Order\Email\SenderBuilderFactory                   $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface                           $logger
     * @param Renderer                                           $addressRenderer
     * @param PaymentHelper                                      $paymentHelper
     * @param OrderResource                                      $orderResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param ManagerInterface                                   $eventManager
     * @param Data                                               $helper
     */
    public function __construct(
        Template $templateContainer,
        OrderIdentity $identityContainer,
        Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        OrderResource $orderResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager,
        Data $helper
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $senderBuilderFactory,
            $logger,
            $addressRenderer,
            $paymentHelper,
            $orderResource,
            $globalConfig,
            $eventManager
        );
        $this->helper = $helper;
    }

    /**
     * @param Order $order
     */
    protected function prepareTemplate(Order $order)
    {
        parent::prepareTemplate($order);

        if ($this->helper->isEnabled()
            && $order->getExtensionAttributes()
            && !$order->getExtensionAttributes()->getMmAgeIsVerified()) {
            $this->templateContainer->setTemplateId($this->helper->getEmailTemplate($order));

            $this->templateContainer->setTemplateVars(
                $this->templateContainer->getTemplateVars()
                + ['verificationUrl' => $this->helper->getAgeVerificationUrlByOrder($order)]
            );
        }
    }
}
