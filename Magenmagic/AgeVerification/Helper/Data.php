<?php

namespace Magenmagic\AgeVerification\Helper;

use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;

/**
 * @category   Magenmagic
 * @package    Magenmagic_AgeVerification
 * @author     Alex Brynov
 */
class Data extends AbstractHelper
{
    /**
     * Path for "Enable module" setting
     */
    const XML_PATH_ENABLE = 'magenmagic_age_verification/general/enabled';

    /**
     * Path for "API username" setting
     */
    const XML_PATH_API_USERNAME = 'magenmagic_age_verification/api/username';

    /**
     * Path for "API password" setting
     */
    const XML_PATH_API_PASSWORD = 'magenmagic_age_verification/api/password';

    /**
     * Path for "Tag Verified" setting
     */
    const XML_PATH_TAG_VERIFIED = 'magenmagic_age_verification/frontend/tag_verified';

    /**
     * Path for "Tag Not Verified" setting
     */
    const XML_PATH_TAG_NOT_VERIFIED = 'magenmagic_age_verification/frontend/tag_not_verified';

    /**
     * Path for "Recipient Email" setting
     */
    const XML_PATH_EMAIL_RECIPIENT_EMAIL = 'magenmagic_age_verification/email/recipient_email';

    /**
     * Path for "Email Template" setting
     */
    const XML_PATH_EMAIL_TEMPLATE = 'magenmagic_age_verification/email/template_order_not_verified';

    /**
     * Path for "Email Template Customer" setting
     */
    const XML_PATH_EMAIL_TEMPLATE_CUSTOMER = 'magenmagic_age_verification/email/template_order_not_verified_customer';

    /**
     * ID of email verification template
     */
    const EMAIL_TEMPLATE_VERIFICATION_REQUEST = 'magenmagic_age_verification_request';

    /**
     * ID of email verification template (for order)
     */
    const EMAIL_TEMPLATE_VERIFICATION_ORDER_REQUEST = 'magenmagic_age_verification_order_request';

    /**
     * Customer EAV attribute for verified flag
     */
    const ATTRIBUTE_CODE_VERIFIED = 'mm_age_is_verified';

    /**
     * Customer EAV attribute for verification doc link
     */
    const ATTRIBUTE_CODE_DOCUMENT_LINK = 'mm_age_verification_doc';

    /**
     * Customer EAV attribute for verification doc link
     */
    const ATTRIBUTE_CODE_ID = 'mm_age_verification_id';

    /**
     * Address column
     */
    const ATTRIBUTE_CODE_DOB = 'mm_dob';

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var ConfigInterface
     */
    protected $contactsConfig;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @param Context            $context
     * @param EncryptorInterface $encryptor
     * @param ConfigInterface    $contactsConfig
     * @param UrlInterface       $url
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        ConfigInterface $contactsConfig,
        UrlInterface $url
    ) {
        $this->encryptor      = $encryptor;
        $this->contactsConfig = $contactsConfig;
        parent::__construct($context);
        $this->url = $url;
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLE,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $storeId
        );
    }

    /**
     * @return string
     */
    public function getApiUsername()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_USERNAME);
    }

    /**
     * @return string
     */
    public function getApiPassword()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::XML_PATH_API_PASSWORD));
    }

    /**
     * @return string
     */
    public function getTagVerified()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_TAG_VERIFIED);
    }

    /**
     * @return string
     */
    public function getTagNotVerified()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_TAG_NOT_VERIFIED);
    }

    /**
     * @return string
     */
    public function getRecipientEmail()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT_EMAIL)
            ?: $this->contactsConfig->emailRecipient();
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    public function getEmailTemplate(Order $order)
    {
        return $this->scopeConfig->getValue(
            $order->getCustomerIsGuest()
                ? self::XML_PATH_EMAIL_TEMPLATE
                : self::XML_PATH_EMAIL_TEMPLATE_CUSTOMER
        );
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    public function getAgeVerificationUrlByOrder(Order $order)
    {
        if ($this->isEnabled() && !$order->getMmAgeIsVerified()) {
            return $this->url->getUrl(
                'age-verification/documents/order',
                ['_query' => ['verification_id' => $this->encryptor->encrypt($order->getId())]]
            );
        }

        return null;
    }
}