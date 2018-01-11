<?php

namespace Magenmagic\AgeVerification\Block\Adminhtml\Order\View;

use Magenmagic\AgeVerification\Helper\Data;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Backend\Block\Template;
use Magento\Sales\Model\Order;

class DocumentLink extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * DocumentLink constructor.
     *
     * @param Context      $context
     * @param Registry     $registry
     * @param Filesystem   $filesystem
     * @param UrlInterface $url
     * @param Data         $dataHelper
     * @param array        $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Filesystem $filesystem,
        UrlInterface $url,
        Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry   = $registry;
        $this->filesystem = $filesystem;
        $this->url        = $url;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return
            $this->isEnabled()
                ? $this->getIsVerifiedHTML() . $this->getLinkHtml()
                : '';
    }

    /**
     * @return Order
     */
    protected function getEntity()
    {
        return $this->registry->registry('sales_order');
    }

    /**
     * @return string
     */
    protected function getLinkHtml()
    {
        $link  = $this->getDocumentLink();
        $label = __('Verification Document');

        if ($link) {
            $viewLabel = __('View');
            $viewLabel = "<a target=\"_blank\" href=\"$link\">$viewLabel</a>";
        } else {
            $viewLabel = __('Pending');
        }

        return $this->_renderItem($label, $viewLabel);
    }

    /**
     * @return string
     */
    protected function getIsVerifiedHTML()
    {
        $label = __('Age Was Verified');
        $value = __($this->getEntity()->getMmAgeIsVerified() ? 'Yes' : 'No');

        return $this->_renderItem($label, $value);
    }

    /**
     * @param $label
     * @param $value
     *
     * @return string
     */
    protected function _renderItem($label, $value)
    {
        return <<<HTML
   <tr>
       <th>$label</th>
       <td>$value</td>
   </tr>
HTML;
    }


    /**
     * @return bool
     */
    protected function isEnabled()
    {
        return $this->dataHelper->isEnabled($this->getEntity()->getStoreId());
    }

    /**
     * @return bool|string
     */
    protected function getDocumentLink()
    {
        if ($link = $this->getEntity()->getMmAgeVerificationDoc()) {
            $mediaDir  = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
            $mediapath = rtrim($mediaDir, '/');

            $localPath = 'age_verification/' . $link;
            $path      = $mediapath . '/' . $localPath;

            if (file_exists($path)) {
                return $this->url->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . $localPath;
            }
        }

        return false;
    }
}