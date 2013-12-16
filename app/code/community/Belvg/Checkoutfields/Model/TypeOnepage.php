<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
 /***************************************
 *         MAGENTO EDITION USAGE NOTICE *
 *****************************************/
 /* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
 /***************************************
 *         DISCLAIMER   *
 *****************************************/
 /* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 *****************************************************
 * @category   Belvg
 * @package    Belvg_Checkoutfields
 * @copyright  Copyright (c) 2010 - 2011 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
   */


class Belvg_Checkoutfields_Model_TypeOnepage extends Mage_Checkout_Model_Type_Onepage{
    public function saveBilling($data, $customerAddressId)
    {        
        $post = Mage::app()->getRequest()->getPost();
        if ($post['attr'])
            $_SESSION['belvg_checkout_params'][] = $post['attr'];
        parent::saveBilling($data, $customerAddressId);
    }
    public function saveShipping($data, $customerAddressId)
    {
        $post = Mage::app()->getRequest()->getPost();
        if ($post['attr'])
            $_SESSION['belvg_checkout_params'][] = $post['attr'];
        parent::saveShipping($data, $customerAddressId);
    }
    public function  saveShippingMethod($shippingMethod) {
        $post = Mage::app()->getRequest()->getPost();
        if ($post['attr'])
            $_SESSION['belvg_checkout_params'][] = $post['attr'];
        parent::saveShippingMethod($shippingMethod);
    }

    public function  savePayment($data) {
        $post = Mage::app()->getRequest()->getPost();
        if ($post['attr'])
            $_SESSION['belvg_checkout_params'][] = $post['attr'];
        parent::savePayment($data);
    }

    public function  saveOrder() {
	$post = Mage::app()->getRequest()->getPost();
        if ($post['attr'])
            $_SESSION['belvg_checkout_params'][] = $post['attr'];
        parent::saveOrder();
        $orderId = $this->getCheckout()->getLastOrderId();
        $params = $_SESSION['belvg_checkout_params'];        
        $_data = array();
        foreach ($params as $b=>$value){
            if (is_array($value)){
                foreach ($value as $key => $v) {
                        if (is_array($v))
                                $_data[$key] = implode(',',$v);
                        else
                                $_data[$key] = $v;
                }
            }else{
                $_data[$b] = $value;
            }
        }
        if ($orderId)
        {
            $model = Mage::getModel('checkoutfields/main');
            $model->saveCustomOrderData($_data,$orderId);            
            $model->clearCheckoutSession();
        }
    }




}


