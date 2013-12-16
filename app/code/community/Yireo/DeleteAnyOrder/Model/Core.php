<?php
/**
 * Yireo DeleteAnyOrder for Magento 
 *
 * @package     Yireo_DeleteAnyOrder
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright (c) 2012 Yireo (http://www.yireo.com/)
 * @license     Open Software License
 */

/**
 * DeleteAnyOrder Core model
 */
class Yireo_DeleteAnyOrder_Model_Core
{
    /**
     * Get a list of all the involved order-models
     *
     * @access public
     * @param null
     * @return array
     */
    public function getOrderModels()
    {
        $models = array(
            'address',
            'invoice',
            'invoice_comment',
            'invoice_item',
            'shipment',
            'shipment_comment',
            'shipment_item',
            'shipment_track',
            'creditmemo',
            'creditmemo_comment',
            'creditmemo_item',
            'status_history',
            'payment',
        );

        foreach($models as $index => $model) {
            $models[$index] = 'sales/order_'.$model;
        }

        return $models;
    }
}
