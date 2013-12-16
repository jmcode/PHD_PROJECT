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
 */?>

<?php


class Belvg_Checkoutfields_Block_List_Grid extends Mage_Adminhtml_Block_Widget_Grid
{	
	public function __construct(){                
	    parent::__construct();
            $this->setId('statusGrid');
            $this->setDefaultSort('created_time');
            $this->setDefaultDir('DESC');
	}

	protected function _prepareCollection(){
            $type='belvg_c';            
            $collection = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter( Mage::getModel('eav/entity')->setType($type)->getTypeId());
           /* $collection->getSelect()->join(
                array('additional_table' => Mage::getResourceModel('eav/entity_attribute')->getTable('catalog/eav_attribute')),
                'additional_table.attribute_id=main_table.attribute_id'
            );*/
             $collection->getSelect()->join(
                array('additional_table2' => Mage::getResourceModel('eav/entity_attribute')->getTable('checkoutfields/fields')),
                'additional_table2.attribute_id=main_table.attribute_id'
            );
			//print_r($collection->count());die;
            $this->setCollection($collection);
            return parent::_prepareCollection();
	}


	public function setFilterValues($data){
		return $this->_setFilterValues($data);
	}

	protected function _prepareColumns(){
		$this->addColumn('attribute_code', array(
                    'header'=>Mage::helper('checkoutfields')->__('Attribute Code'),
                    'sortable'=>true,
                    'index'=>'attribute_code'
                ));

                $this->addColumn('frontend_label', array(
                    'header'=>Mage::helper('eav')->__('Attribute Label'),
                    'sortable'=>true,
                    'index'=>'frontend_label',                    
                ));

                $this->addColumn('frontend_input', array(
                    'header'=>Mage::helper('eav')->__('Input type'),
                    'sortable'=>true,
                    'index'=>'frontend_input',
                    'type' => 'options',
                    'options' => array(
                        'text'          => Mage::helper('checkoutfields')->__('Text Field'),
                        'textarea'      => Mage::helper('checkoutfields')->__('Text Area'),
                        'date'          => Mage::helper('checkoutfields')->__('Date'),
                        'boolean'       => Mage::helper('checkoutfields')->__('Yes/No'),
                        'multiselect'   => Mage::helper('checkoutfields')->__('Multiple Select'),
                        'select'        => Mage::helper('checkoutfields')->__('Dropdown'),
                        'checkbox'      => Mage::helper('checkoutfields')->__('Checkbox'),
                        'radio'         => Mage::helper('checkoutfields')->__('Radiobutton'),
                        'static'         => Mage::helper('checkoutfields')->__('Static Text'),
                    ),
                ));

                $this->addColumn('checkout_step', array(
                    'header'=>Mage::helper('catalog')->__('Checkout Step'),
                    'sortable'=>true,
                    'index'=>'checkout_step',
                    'type' => 'options',
                    'options' => array(
                        '0' => '1. Billing Information',
                        '1' => '2. Shipping Information',
                        '2' => '3. Shippping Method',
                        '3' => '4. Payment Information',
                        '4' => '5. Order Review',
                        '7' => 'Free Position'
                    ),

                ));

                $this->addColumn('is_required', array(
                    'header'=>Mage::helper('eav')->__('Required'),
                    'sortable'=>true,
                    'index'=>'is_required',
                    'type' => 'options',
                    'options' => array(
                        '1' => Mage::helper('eav')->__('Yes'),
                        '0' => Mage::helper('eav')->__('No'),
                    ),
                    'align' => 'center',
                ));
                 $this->addColumn('is_enabled', array(
                    'header'=>Mage::helper('eav')->__('Enabled'),
                    'sortable'=>true,
                    'index'=>'is_enabled',
                    'type' => 'options',
                    'options' => array(
                        '1' => Mage::helper('eav')->__('Yes'),
                        '0' => Mage::helper('eav')->__('No'),
                    ),
                    'align' => 'center',
                ));
                $this->addColumn('action',
                    array(
                        'header'    =>  $this->__('Action'),
                        'width'     => '100px',
                        'type'      => 'action',
                        'getter'    => 'getId',
                        'actions'   => array(

                                                array(
                                'caption'   => $this->__('Delete'),
                                'url'       => array('base'=> '*/*/delete'),
                                'field'     => 'id'
                            )
                        ),
                        'filter'    => false,
                        'sortable'  => false,
                        'index'     => 'stores',
                        'is_system' => true,
                ));
		return parent::_prepareColumns();
	}

	
	
	protected function _toHtml(){
		return Mage::app()->getLayout()->createBlock('adminhtml/store_switcher')->toHtml().parent::_toHtml();
	}	

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}

