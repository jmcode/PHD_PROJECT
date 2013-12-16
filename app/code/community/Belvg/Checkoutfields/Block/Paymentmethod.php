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

class Belvg_Checkoutfields_Block_Paymentmethod extends Mage_Checkout_Block_Onepage_Billing
{
   

    public function getItemsHtml(){
        $form = new Varien_Data_Form();
        $form
                 ->setId('design_checkoutfield')
                 ->setName('design')
                 ->setMethod('POST')
                 ->setUseContainer(true);
        $fieldset = $form->addFieldset('checkoutfields', array('legend'=>Mage::helper('core')->__('')));
        $type='belvg_c';
        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter( Mage::getModel('eav/entity')->setType($type)->getTypeId());
        
        $collection->getSelect()->join(
            array('additional_table2' => Mage::getResourceModel('eav/entity_attribute')->getTable('checkoutfields/fields')),
            'additional_table2.attribute_id=main_table.attribute_id'
        );
		$yesnoSource = Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray();
        foreach ($collection as $_item){            
            if ($_item->getCheckoutStep() == 3 && $_item->getIsEnabled() == 1){
                if ($_item->getIsRequired()) $required = true; else $required = false;
                if ($_item->getFrontendInput() == 'select' or $_item->getFrontendInput() == 'multiselect'){                   
                   $element = $fieldset->addField($_item->getFrontendLabel(), $_item->getFrontendInput(), array(
                        'label'    => Mage::helper('core')->__($_item->getFrontendLabel()),
                        'title'    => Mage::helper('core')->__($_item->getFrontendLabel()),
                        'name'     => 'attr['.$_item->getAttributeId().']',
                        'required' => $required,
                        'width'    => '50px',
						'values'   => Mage::getModel('checkoutfields/main')->getAllOptions($_item->getAttributeId())
                    ));
					
                }elseif($_item->getFrontendInput() == 'date'){
					$fieldset->addField($_item->getFrontendLabel(), $_item->getFrontendInput(), array( 
					 'name'     => 'attr['.$_item->getAttributeId().']',
					 'label'    => Mage::helper('core')->__($_item->getFrontendLabel()),
                     'title'    => Mage::helper('core')->__($_item->getFrontendLabel()),
					 'image'  => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif',
					 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
					 'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
					 ));
				}
				elseif($_item->getFrontendInput() == 'media_image'){
					$fieldset->addField($_item->getFrontendLabel(), 'imagefile', array( 
						'label'    => Mage::helper('core')->__($_item->getFrontendLabel()),
                        'title'    => Mage::helper('core')->__($_item->getFrontendLabel()),
                        'name'     => 'attr['.$_item->getAttributeId().']',
                        'required' => $required,
                        'width'    => '50px',
					 ));
				}
				elseif($_item->getFrontendInput() == 'boolean'){
					$fieldset->addField($_item->getFrontendLabel(), 'select', array(
                        'label'    => Mage::helper('core')->__($_item->getFrontendLabel()),
                        'title'    => Mage::helper('core')->__($_item->getFrontendLabel()),
                        'name'     => 'attr['.$_item->getAttributeId().']',
                        'required' => $required,
                        'width'    => '50px',
						'values'   => $yesnoSource
                    ));
				}
                else{
                    $fieldset->addField($_item->getAttributeId(), $_item->getFrontendInput(), array(
                        'label'    => Mage::helper('core')->__($_item->getFrontendLabel()),
                        'title'    => Mage::helper('core')->__($_item->getFrontendLabel()),
                        'name'     => 'attr['.$_item->getAttributeId().']',
                        'required' => $required,
                        'width'    => '50px',
                    ));

                }

            }
        }        

        $formData = array();
        $form->addValues($formData);

        return $form->getHtml();
    }
}