<?php

namespace Magenmagic\AgeVerification\Plugin\Checkout\Block\Checkout;

use Magenmagic\AgeVerification\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Checkout\Block\Checkout\LayoutProcessor as BaseLayoutProcessor;
use \Magento\Checkout\Model\Session as CheckoutSession;

class LayoutProcessor
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * LayoutProcessor constructor.
     *
     * @param Data            $helper
     * @param Session         $session
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(Data $helper, Session $session, CheckoutSession $checkoutSession)
    {
        $this->helper          = $helper;
        $this->session         = $session;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param BaseLayoutProcessor $subject
     * @param array               $jsLayout
     *
     * @return array
     */
    public function afterProcess(BaseLayoutProcessor $subject, array $jsLayout)
    {
        if ($this->helper->isEnabled() && !$this->session->isLoggedIn()) {
            $customAttributeCode = 'mm_dob';
            $customField         = [
                'component'   => 'Magento_Ui/js/form/element/abstract',
                'config'      => [
                    // customScope is used to group elements within a single form (e.g. they can be validated separately)
                    'customScope' => 'billingAddress.custom_attributes',
                    'customEntry' => null,
                    'template'    => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/date',
                ],
                'dataScope'   => 'billingAddress.custom_attributes' . '.' . $customAttributeCode,
                'label'       => 'Date of Birth',
                'provider'    => 'checkoutProvider',
                'sortOrder'   => 0,
                'validation'  => [
                    'required-entry' => true
                ],
                'options'     => [],
                'filterBy'    => null,
                'customEntry' => null,
                'visible'     => true,
            ];

            if ($this->checkoutSession->getQuote()->isVirtual()) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['beforeMethods']['children'][$customAttributeCode] =
                    $customField;
            } else {
                foreach (
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']
                    as $key => &$item
                ) {
                    if (preg_match('/-form$/', $key) && isset($item['children']['form-fields'])) {
                        $item['children']['form-fields']['children'][$customAttributeCode] = $customField;
                    }
                }

                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$customAttributeCode] =
                    $customField;
            }


//            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
//            ['payment']['children']['beforeMethods']['children']['dob'] = [
//                'component' => 'Magento_Ui/js/form/element/abstract',
//
//                'config'     => [
//                    'customScope' => 'billingAddress',
//                    'required'    => true,
//                    'template'    => 'ui/form/field',
//                    'elementTmpl' => 'ui/form/element/date',
//                    'options'     => [],
//                    'id'          => 'dob',
//                    'name'        => 'aaaa',
//                    'inputName'   => 'dob',
//                ],
//                'validation' => [
//                    'required-entry' => true,
//                ],
//                'dataScope'  => 'billingAddress.dob',
//                'provider'   => 'checkoutProvider',
//                'label'      => 'Date of Birth',
//                'inputName'  => 'dob',
//                'visible'    => true,
//                'sortOrder'  => 10,
//                'id'         => 'dob'
//            ];
        }

        return $jsLayout;
    }
}


