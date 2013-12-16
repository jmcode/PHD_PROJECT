<?php
/**
 * Atwix
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Atwix
 * @package     Atwix_ExtendedGrid
 * @author      Atwix Core Team
 * @copyright   Copyright (c) 2012 Atwix (http://www.atwix.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Atwix_ExtendedGrid_Model_Observer
{
    /**
     * Joins extra tables for adding custom columns to Mage_Adminhtml_Block_Sales_Order_Grid
     * @param Varien_Object $observer
     * @return Atwix_Exgrid_Model_Observer
     */
    public function salesOrderGridCollectionLoadBefore($observer)
    {
        $collection = $observer->getOrderGridCollection();
        $select = $collection->getSelect();
        //$select->joinLeft(array('payment' => $collection->getTable('sales/order_payment')), 'payment.parent_id=main_table.entity_id', array('payment_method' => 'method'));
        $select->joinLeft(
            array('order' => $collection->getTable('sales/order')), 'order.entity_id=main_table.entity_id', array('expected_delivery' => 'expected_delivery', 'dispatch_date' => 'dispatch_date'));
        //$select->joinLeft(array('order' => $collection->getTable('sales/order')), 'order.entity_id=main_table.entity_id', array('expected_delivery' => 'expected_delivery', 'shipping_description' => 'shipping_description', 'dispatch_date' => 'dispatch_date', 'coupon_code' => 'coupon_code'));
        //$select->joinLeft(array('orderatts' => $collection->getTable('checkoutfields/orders')), 'orderatts.order_id=main_table.entity_id', array('delivery_instructions' => 'value'));
    }
}