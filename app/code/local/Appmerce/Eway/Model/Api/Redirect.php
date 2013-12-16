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
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Appmerce_Eway_Model_Api_Redirect extends Appmerce_Eway_Model_Api
{
    protected $_code = 'eway_redirect';

    /**
     * Get redirect URL after placing order
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->getConfig()->getApiUrl('placement');
    }

    /**
     * Generates array of fields for redirect form
     *
     * @return array
     */
    public function getRedirectFields($order)
    {
        if (empty($order)) {
            if (!($order = $this->getOrder())) {
                return array();
            }
        }

        $storeId = $order->getStoreId();
        $billingAddress = $order->getBillingAddress();
        $paymentMethodCode = $order->getPayment()->getMethod();

        $redirectFields = array();
        $redirectFields['CustomerID'] = $this->getConfigData('customer_id', $storeId);
        $redirectFields['UserName'] = $this->getConfigData('username', $storeId);
        // Redirect amounts have 2 digits, e.g. 12.99
        $redirectFields['Amount'] = number_format($order->getBaseGrandTotal(), 2, '.', '');
        $redirectFields['Currency'] = $this->getCurrencyCode();
        $redirectFields['ReturnURL'] = $this->getConfig()->getApiUrl('return', $storeId);
        $redirectFields['CancelURL'] = $this->getConfig()->getApiUrl('cancel', $storeId);
        $redirectFields['ModifiableCustomerDetails'] = $this->getConfig()->getModifiableDetails($this->getConfigData('modifiable_details'));
        $redirectFields['Language'] = $this->getConfigData('interface_language', $storeId);

        // Template constants
        $redirectFields['PageTitle'] = $this->getConfig()->getServiceConfigData('page_title', $storeId);
        $redirectFields['PageDescription'] = $this->getConfig()->getServiceConfigData('page_description', $storeId);
        $redirectFields['PageFooter'] = $this->getConfig()->getServiceConfigData('page_footer', $storeId);
        $redirectFields['CompanyName'] = $this->getConfig()->getServiceConfigData('company_name', $storeId);
        $redirectFields['CompanyLogo'] = $this->getConfig()->getServiceConfigData('company_logo', $storeId);
        $redirectFields['Pagebanner'] = $this->getConfig()->getServiceConfigData('page_banner', $storeId);

        // Customer data
        $redirectFields['CustomerFirstName'] = $billingAddress->getFirstname();
        $redirectFields['CustomerLastName'] = $billingAddress->getLastname();
        $redirectFields['CustomerAddress'] = $billingAddress->getStreet(1);
        $redirectFields['CustomerCity'] = $billingAddress->getCity();
        $redirectFields['CustomerState'] = $billingAddress->getRegion();
        $redirectFields['CustomersPostCode'] = $billingAddress->getPostcode();
        $redirectFields['CustomerCountry'] = $billingAddress->getCountry();
        $redirectFields['CustomerPhone'] = $billingAddress->getTelephone();
        $redirectFields['CustomerEmail'] = $billingAddress->getEmail();

        // Invoice
        $redirectFields['InvoiceDescription'] = $this->getConfig()->getOrderDescription($order);
        $redirectFields['MerchantReference'] = $order->getId();
        $redirectFields['MerchantInvoice'] = $order->getIncrementId();

        return $redirectFields;
    }

    /**
     * Get result fields
     *
     * @param $params array
     * @return array
     */
    public function getResultFields($params)
    {
        $storeId = Mage::app()->getStore()->getId();
        $resultFields = array();
        $resultFields['CustomerID'] = $this->getConfigData('customer_id', $storeId);
        $resultFields['UserName'] = $this->getConfigData('username', $storeId);
        $resultFields['AccessPaymentCode'] = $params['AccessPaymentCode'];
        return $resultFields;
    }

}
