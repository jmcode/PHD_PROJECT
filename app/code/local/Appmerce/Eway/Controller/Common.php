<?php
/**
 * Appmerce - Applications for Ecommerce
 * http://ww.appmerce.com
 *
 * @extension   eWAY Hosted Payment (AU/UK/NZ), XML+CVN (AU), Rapid API
 * @type        Payment method
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Magento Commerce
 * @package     Appmerce_Eway
 * @copyright   Copyright (c) 2011-2012 Appmerce (http://www.appmerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.	php  Open Software License (OSL 3.0)
 */

class Appmerce_Eway_Controller_Common extends Mage_Core_Controller_Front_Action
{
    /**
     * Return checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Return order process instance
     *
     * @return Appmerce_Eway_Model_Process
     */
    public function getProcess()
    {
        return Mage::getSingleton('eway/process');
    }

    /**
     * Return order instance by LastOrderId
     *
     * @return  Mage_Sales_Model_Order object
     */
    protected function getLastRealOrder()
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($this->getCheckout()->getLastRealOrderId());
        return $order;
    }

    /**
     * Save checkout session
     */
    public function saveCheckoutSession()
    {
        $this->getCheckout()->setEwayQuoteId($this->getCheckout()->getLastSuccessQuoteId());
        $this->getCheckout()->setEwayOrderId($this->getCheckout()->getLastOrderId(true));
    }

}
