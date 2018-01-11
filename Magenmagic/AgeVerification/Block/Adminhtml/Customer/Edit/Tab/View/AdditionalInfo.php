<?php

namespace Magenmagic\AgeVerification\Block\Adminhtml\Customer\Edit\Tab\View;

use Magenmagic\AgeVerification\Block\Adminhtml\Order\View\DocumentLink;
use Magento\Framework\DataObject;

class AdditionalInfo extends DocumentLink
{
    /**
     * @var DataObject
     */
    private $customer;

    /**
     * @return DataObject
     */
    protected function getEntity()
    {
        if ($this->customer === null) {
            $customerData = $this->_backendSession->getCustomerData();
            if ($data = array_intersect_key(
                $customerData['account'],
                array_flip(array('mm_age_is_verified', 'mm_age_verification_doc', 'store_id'))
            )) {
                $this->customer = new DataObject($data);
            }
        }


        return $this->customer;
    }

    /**
     * @return bool
     */
    protected function isEnabled()
    {
        if (!$this->getEntity()) {
            return false;
        }

        return parent::isEnabled();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();

        if ($html) {
            $html = <<<HTML
            <div class="fieldset-wrapper customer-information">
<table class="admin__table-secondary">
        <tbody>
        $html
</tbody>
</table>
</div>
HTML;

        }

        return $html;
    }
}
