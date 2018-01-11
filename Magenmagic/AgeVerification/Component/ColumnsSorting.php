<?php

namespace Magenmagic\AgeVerification\Component;

use Magento\Framework\View\Element\UiComponent\ObserverInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\AbstractComponent;
use Magento\Ui\Component\Listing\Columns\ColumnInterface;

/**
 * Fix for missing sorting of columns in admin grid
 */
class ColumnsSorting extends AbstractComponent implements ObserverInterface
{
    /**
     * @var int
     */
    private static $index = 0;

    /**
     * @param UiComponentInterface $component
     */
    public function update(UiComponentInterface $component)
    {
        if ($component instanceof ColumnInterface) {
            $configuration = $component->getConfiguration();
            if (!array_key_exists('sortOrder', $configuration)) {
                $component->setData('config', $configuration + ['sortOrder' => self::$index * 10]);
                ++self::$index;
            } else {
                $a = 1;
            }
        }
    }

    /**
     * @return string
     */
    public function getComponentName()
    {
        return 'columns_sorting';
    }
}