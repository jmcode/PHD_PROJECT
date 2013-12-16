<?php

class Webshopapps_Timegrid_Block_Adminhtml_Timegrid_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('timegrid_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('timegrid')->__('WebShopApps Delivery Prices'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('timegrid')->__('Weekly Prices'),
          'title'     => Mage::helper('timegrid')->__('Weekly Prices'),
          'content'   => $this->getLayout()->createBlock('timegrid/adminhtml_timegrid_edit_tab_form')->toHtml(),
      ));

      return parent::_beforeToHtml();
  }
}