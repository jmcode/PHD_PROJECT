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


class Belvg_Checkoutfields_Block_Rewrite_AdminSalesOrderViewInfo  extends Mage_Adminhtml_Block_Sales_Order_View_Info
{
    public function getOrderCustomData()
    {
        $storeId = $this->getOrder()->getStoreId();
        $frontController = Mage::app()->getFrontController();
        $orderId = $oFront->getRequest()->getParam('order_id');
        $checkoutFields  = Mage::getModel('checkoutfields/main')->getCustomFieldsData($storeId,$orderId);
        return $checkoutFields;
    }

     public function getCustomItemsHtml(){
        $form = new Varien_Data_Form();
        $form->setAction($this->getBaseUrl().'custattributes/profile/saveGroup/id/'.$group_id.'/')
                 ->setId('design_customerattr')
                 ->setName('design')
                 ->setMethod('POST')
                 ->setUseContainer(true);
        $fieldset = $form->addFieldset('customerattr', array('legend'=>Mage::helper('core')->__('')));
        $items = Mage::getResourceModel('custattributes/attributes_collection')
            ->addGroupFilter($group_id)
            ->addStoreFilter($this->helper('core')->getStoreId())
            ->addVisibleFilter()
            ->load();

        foreach ($items as $_item){
            
            if ($_item->getType() == 'select' or $_item->getType() == 'multiselect'){
               $fieldset->addField($_item->getTitle(), $_item->getType(), array(
                    'label'    => Mage::helper('core')->__($_item->getTitle()),
                    'title'    => Mage::helper('core')->__($_item->getTitle()),
                    'name'     => 'attr['.$_item->getAttributeId().']',
                    'values'   => Mage::getModel('custattributes/attributes')->getOptions($_item),
                    'width'    => '50px',
                ));
            }
            else{
                $fieldset->addField($_item->getAttributeId(), $_item->getType(), array(
                    'label'    => Mage::helper('core')->__($_item->getTitle()),
                    'title'    => Mage::helper('core')->__($_item->getTitle()),
                    'name'     => 'attr['.$_item->getAttributeId().']',
                    'width'    => '50px',
                ));

            }
        }

        $formData = Mage::getModel('custattributes/attributes')->getByGroupCustomer($group_id,Mage::getSingleton('customer/session')->getCustomer()->getId());
        $form->addValues($formData);

        return $form->getHtml();
    }

}

