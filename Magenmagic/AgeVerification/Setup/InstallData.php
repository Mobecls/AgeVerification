<?php

namespace Magenmagic\AgeVerification\Setup;

use Magenmagic\AgeVerification\Helper\Data;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @category   Magenmagic
 * @package    Magenmagic_AgeVerification
 * @author     Alex Brynov
 *
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * Init
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param SalesSetupFactory    $salesSetupFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->salesSetupFactory    = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        /**
         * Due to known bug, it is not possible for now to add custom attribute to grid
         * https://github.com/magento/magento2/issues/10838
         */
        $customerSetup->addAttribute(
            Customer::ENTITY,
            Data::ATTRIBUTE_CODE_VERIFIED,
            [
                'label'    => 'Age Is Verified',
                'type'     => 'int',
                'input'    => 'select',
                'position' => 140,
                'visible'  => true,
                'required' => false,
                'system'   => false,
                'source'   => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'default'  => 0,
                //'is_used_in_grid' => true,
            ]
        );

        $customerSetup->addAttribute(
            Customer::ENTITY,
            Data::ATTRIBUTE_CODE_DOCUMENT_LINK,
            [
                'label'    => 'Age Verification Document',
                'type'     => Table::TYPE_TEXT,
                'position' => 150,
                'visible'  => false,
                'required' => false,
                'system'   => false,
                'length'   => 255,
                //'is_used_in_grid' => true,
            ]
        );

        $customerSetup->addAttribute(
            Customer::ENTITY,
            Data::ATTRIBUTE_CODE_ID,
            [
                'label'    => 'Age Verification ID',
                'type'     => Table::TYPE_TEXT,
                'position' => 150,
                'visible'  => false,
                'required' => false,
                'system'   => false,
                'length'   => 255,
                //'is_used_in_grid' => true,
            ]
        );

        $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, Data::ATTRIBUTE_CODE_VERIFIED)
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        /**
         * New Columns in order table
         */
        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */

        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        $salesSetup->addAttribute(
            'order',
            Data::ATTRIBUTE_CODE_VERIFIED,
            [
                'label'           => 'Age Is Verified',
                'visible'         => true,
                'default'         => 0,
                'required'        => false,
                'type'            => Table::TYPE_SMALLINT,
                'is_used_in_grid' => true,
                'input'           => 'select',
                'source'          => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'grid'            => true,
            ]
        );

        $salesSetup->addAttribute(
            'order',
            Data::ATTRIBUTE_CODE_DOCUMENT_LINK,
            [
                'label'    => 'Age Verification Document',
                'visible'  => false,
                'default'  => '',
                'required' => false,
                'type'     => Table::TYPE_TEXT,
                'grid'     => true,
                'length'   => 255,
            ]
        );

        $salesSetup->addAttribute(
            'order',
            Data::ATTRIBUTE_CODE_ID,
            [
                'label'    => 'Age Verification ID',
                'visible'  => false,
                'default'  => '',
                'required' => false,
                'type'     => Table::TYPE_TEXT,
                'grid'     => true,
                'length'   => 255,
            ]
        );

        $salesSetup->getConnection()->addColumn(
            $setup->getTable('sales_order_grid'),
            Data::ATTRIBUTE_CODE_VERIFIED,
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'length'   => 5,
                'nullable' => true,
                'comment'  => 'Magenmagic_AgeVerification Is Age Verified'
            ]
        );

        foreach (['quote_address', 'sales_order_address'] as $table) {
            $salesSetup->getConnection()->addColumn(
                $salesSetup->getTable($table),
                Data::ATTRIBUTE_CODE_DOB,
                [
                    'type'    => 'date',
                    'comment' => 'Magenmagic_AgeVerification Date Of Birth'
                ]
            );
        }

        $setup->endSetup();
    }
}
