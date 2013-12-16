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


class Belvg_Checkoutfields_Model_Main extends Mage_Core_Model_Abstract{
    protected function _construct(){
	$this->_init('checkoutfields/main');
    }

    public function getCheckoutSteps(){
        $_steps = array(
            array(
                'value' => '0',
                'label' => '1. Billing Information'
            ),
            array(
                'value' => '1',
                'label' => '2. Shipping Information'
            ),
            array(
                'value' => '2',
                'label' => '3. Shippping Method'
            ),
            array(
                'value' => '3',
                'label' => '4. Payment Information'
            ),    
            array(
                'value' => '7',
                'label' => 'Free Position'
            )

        );
        return $_steps;
    }

    public function saveCustomFields($data,$idflag){        
        $_field = Mage::getModel('checkoutfields/fields')->loadByAttributeId($idflag);              
        $_field->addData($data);
        try{
            $_field->save();
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

    }


    public function getCustomData($id){
        $_field = Mage::getModel('checkoutfields/fields')->loadByAttributeId($id);
        return $_field->getData();
    }

    public function getCustomFields($store_id){

    }

    public function saveCustomOrderData($data,$orderId){
        try{
            foreach ($data as $key => $value){
                $proData = array(
                    'attribute_id' => $key,
                    'order_id' => $orderId,
                    'value' => $value
                );
                $order = Mage::getModel('checkoutfields/orders')->addData($proData);
                $order->save();
            }
        
        }catch(Exception $e){
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
        }   
        
        return true;
    }

    public function clearCheckoutSession(){
        unset($_SESSION['belvg_checkout_params']);
        $_SESSION['belvg_checkout_params'] = '';
    }

    public function getAdminOrderData($_orderId,$admin){
        $_data = array();
        try{
			if ($admin){				
                                $_orderAttributes = Mage::getModel('checkoutfields/orders')->loadByOrderId($_orderId);
                                foreach($_orderAttributes as $_oA){    
                                        $_attribute = Mage::getModel('checkoutfields/fields')->loadByAttributeId($_oA->getAttributeId());
					$flag_show = ($_attribute->getAdminShow() == 1)?true:false;
					$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($_attribute->getAttributeId());
                                        $tmp_data = $_oA->getData();
                                        $tmp_data['label'] = $attribute->getFrontendLabel();
                                        if ($attribute->getFrontendInput() == 'boolean'){
                                            $tmp_data['value'] = ( $_oA->getValue() == 1)?'Yes':'No';
                                        }
					if ($flag_show != false) $_data[] = $tmp_data;
					

				}
			}else{
				
				$_orderAttributes = Mage::getModel('checkoutfields/orders')->loadByOrderId($_orderId);
                                foreach($_orderAttributes as $_oA){   					
					$_attribute = Mage::getModel('checkoutfields/fields')->loadByAttributeId($_oA->getAttributeId());
					$flag_show = ($_attribute->getCustomerShow() == 1)?true:false;
					$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($_attribute->getAttributeId());
                                        $tmp_data = $_oA->getData();
                                        $tmp_data['label'] = $attribute->getFrontendLabel();
                                        if ($attribute->getFrontendInput() == 'boolean'){
                                            $tmp_data['value'] = ( $_oA->getValue() == 1)?'Yes':'No';
                                        }
					if ($flag_show != false) $_data[] = $tmp_data;
					

				}
			}	
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }        
        return $_data;
    }
	
	public function insertToCEA($id){
		$model = Mage::getModel('catalog/resource_eav_attribute');
                $model->addData(array(
                    'id' => $id,
                    'is_global' => true,
                    'is_visible' => true,
                    'is_configurable' => true
                ));
		try{			
                    $model->save();
		}catch(Exception $e){
                    Mage::getSingleton('checkout/session')->addError($e->getMessage());
                }                       
	}
	
	
	public function getAllOptions($id){
                $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($id);
                $options = $attribute->getSource()->getAllOptions(false);
		return $options;		
	}

}

