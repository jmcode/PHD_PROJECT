<?php

/*
 * @author     Kristof Ringleff
 * @package    Fooman_Connect
 * @copyright  Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 */

class Fooman_Connect_Block_Adminhtml_Sales_Order_Xero extends Mage_Adminhtml_Block_Sales_Order_Abstract {

    protected function _construct() {
        $this->setTemplate('fooman/connect/sales/order/view/info-xero.phtml');
    }

    /* export not yet working with AJAX - forward to main page instead
    protected function _prepareLayout()
    {
        $onclick = "submitAndReloadArea($('foomanconnect_block').parentNode, '".$this->getSubmitUrl()."')";
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id'      => 'export_now_button',
                'label'   => Mage::helper('foomanconnect')->__('Export now'),
                'class'   => 'save',
                'onclick' => $onclick
            ));
        $this->setChild('export_now_button', $button);
        return parent::_prepareLayout();
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('adminhtml/xero/processOne',array('order_id'=>$this->getOrder()->getId()));
    }
    */

    protected function _prepareLayout() {
        $onclick = "document.location.href='" . $this->getSubmitUrl() . "'";
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'id'      => 'export_now_button',
                'label'   => Mage::helper('foomanconnect')->__('Export now'),
                'class'   => 'save',
                'onclick' => $onclick
        ));
        $this->setChild('export_now_button', $button);
        return parent::_prepareLayout();
    }

    public function getSubmitUrl() {
        return $this->getUrl('adminhtml/xero/');
    }

    public function getOrder() {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);
        return $order;
    }

    public function getXeroPayments() {
        return Mage::getModel('foomanconnect/xeroOauth')->getPaymentsFromXero($this->getOrder());
    }

    public function getXeroInvoiceUrl() {
        $xeroInvoiceId = $this->getOrder()->getXeroInvoiceId();
        return Fooman_Connect_Model_XeroOauth::CA_XERO_INVOICE_LINK.$xeroInvoiceId;
    }

    public function displayPayments() {
        return Mage::helper('foomanconnect')->getMageStoreConfig('xeropayments');
    }

    public function isExported() {
        return ($this->getOrder()->getXeroExportStatus() == Fooman_Connect_Model_XeroOauth::CA_XERO_STATUS_ORDER_EXPORTED);
    }

    public function getXeroLastValidationErrors() {
        $validationErrors = $this->getOrder()->getXeroLastValidationErrors();
        if($validationErrors) {
            $validationErrorsArray = unserialize($validationErrors);
            if(!empty($validationErrorsArray)) {
                return $validationErrorsArray;
            }
        }
        return array();
    }

}