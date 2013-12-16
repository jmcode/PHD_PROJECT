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

class Appmerce_Eway_Model_Api_Direct extends Appmerce_Eway_Model_Api
{
    protected $_code = 'eway_direct';
    protected $_formBlockType = 'eway/form_direct';

    // Magento features
    protected $_canSaveCc = false;
    protected $_canRefund = true;
    protected $_canRefundPartial = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;

    /**
     * Check refund availability
     *
     * @return bool
     */
    public function canRefund()
    {
        // UK does not support refunds
        if ($this->getConfigData('subscription') == Appmerce_Eway_Model_Config::COUNTRY_UK) {
            $this->_canRefund = false;
        }
        return $this->_canRefund;
    }

    /**
     * Check capture availability
     *
     * @return bool
     */
    public function canCapture()
    {
        // UK does not support Token Payments
        if ($this->getConfigData('subscription') == Appmerce_Eway_Model_Config::COUNTRY_UK) {
            $this->_canCapture = false;
        }
        if (!$this->getConfigData('token_payments')) {
            $this->_canCapture = false;
        }
        return $this->_canCapture;
    }

    /**
     * Refund specified amount for payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Payment_Model_Abstract
     */
    public function refund(Varien_Object $payment, $amount)
    {
        if (!$this->canRefund()) {
            Mage::throwException(Mage::helper('eway')->__('Refund action is not available.'));
        }

        // Refund through eWAY
        if ($payment->getRefundTransactionId() && $amount > 0) {
            $order = $payment->getOrder();
            if ($order->getId()) {

                // Build gateway URL and $directFields XML message
                $refundFields = $this->getRefundFields($order);
                $xmlMessage = $this->createXmlMessage($refundFields);

                // Send request, receive response
                $type = Appmerce_Eway_Model_Config::TYPE_REFUND;
                $gateway = $this->getGatewayUrl($type);
                $request = $this->curlPostXml($gateway, $xmlMessage);
                $response = new SimpleXMLElement($request);

                // Debug
                if ($this->getConfigData('debug_flag')) {
                    Mage::getModel('eway/api_debug')->setDir('out')->setUrl('')->setData('data', print_r($refundFields, true))->save();
                    Mage::getModel('eway/api_debug')->setDir('in')->setUrl('')->setData('data', $request)->save();
                }

                $transactionId = (string)$response->ewayTrxnNumber;
                $responseCode = substr((string)$response->ewayTrxnError, 0, 2);
                $note = $this->getConfig()->getResponseMessage($responseCode);

                // Switch response
                switch ((string)$response->ewayTrxnStatus) {
                    case Appmerce_Eway_Model_Api::STATUS_FALSE :
                        Mage::throwException(Mage::helper('eway')->__('eWAY refund failed.'));
                        break;

                    case Appmerce_Eway_Model_Api::STATUS_TRUE :
                    default :
                }
            }
            else {
                Mage::throwException(Mage::helper('eway')->__('Invalid order for refund.'));
            }
        }
        else {
            Mage::throwException(Mage::helper('eway')->__('Invalid transaction for refund.'));
        }

        return $this;
    }

    /**
     * Capture payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        if (!$this->canCapture()) {
            Mage::throwException(Mage::helper('eway')->__('Capture action is not available.'));
        }

        if ($amount > 0) {
            $order = $payment->getOrder();
            if ($order->getId()) {
                $tokenHeaders = $this->getTokenHeaders($order);
                $tokenFields = array(
                    'managedCustomerID' => (string)$order->getPayment()->getEwayManagedAlias(),
                    'amount' => (int)round($amount * 100),
                    'invoiceReference' => (string)$order->getIncrementId(),
                    'invoiceDescription' => (string)$this->getConfig()->getOrderDescription($order),
                );

                // Build SOAP request
                $soapType = Appmerce_Eway_Model_Config::TYPE_TOKEN;
                $soapClient = new SoapClient($this->getGatewayUrl($soapType), array('trace' => 1));
                $soapHeaders = new SoapHeader($this->getSoapHeaderUrl($soapType), 'eWAYHeader', $tokenHeaders);
                $soapClient->__setSoapHeaders(array($soapHeaders));

                // SOAP Response
                try {
                    $response = $soapClient->ProcessPayment($tokenFields, $soapHeaders);
                }
                catch (SoapFault $e) {
                    Mage::throwException(Mage::helper('eway')->__('eWAY capture failed: %s', $e->faultstring));
                }

                // Debug
                if ($this->getConfigData('debug_flag')) {
                    Mage::getModel('eway/api_debug')->setDir('out')->setUrl('')->setData('data', print_r($tokenFields, true))->save();
                    Mage::getModel('eway/api_debug')->setDir('in')->setUrl('')->setData('data', $response)->save();
                }

                $transactionId = (string)$response->ewayResponse->ewayTrxnNumber;
                $responseCode = substr((string)$response->ewayResponse->ewayTrxnError, 0, 2);
                $note = $this->getConfig()->getResponseMessage($responseCode);

                // Switch response
                switch ((string)$response->ewayResponse->ewayTrxnStatus) {
                    case Appmerce_Eway_Model_Api::STATUS_FALSE :
                        Mage::throwException(Mage::helper('eway')->__('eWAY capture failed: %s', $note));
                        break;

                    case Appmerce_Eway_Model_Api::STATUS_TRUE :
                        $order->getPayment()->setAppmerceResponseCode($responseCode);
                        $order->getPayment()->setTransactionId($transactionId);
                        $order->getPayment()->setLastTransId($transactionId);
                        $order->save();

                    default :
                }
            }
            else {
                Mage::throwException(Mage::helper('eway')->__('Invalid order for capture.'));
            }
        }
        else {
            Mage::throwException(Mage::helper('eway')->__('Invalid amount for capture.'));
        }

        return $this;
    }

    /**
     * Get redirect URL after placing order
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->getConfig()->getDirectUrl('placement');
    }

    /**
     * Generates array of fields for redirect form
     *
     * @return array
     */
    public function getDirectFields($order)
    {
        if (empty($order)) {
            if (!($order = $this->getOrder())) {
                return array();
            }
        }

        $storeId = $order->getStoreId();
        $billingAddress = $order->getBillingAddress();
        $paymentMethodCode = $order->getPayment()->getMethod();

        $directFields = array();
        $directFields['ewayCustomerID'] = substr($this->getConfigData('customer_id', $storeId), 0, 8);
        $amount = number_format($order->getBaseGrandTotal(), 2, '.', '');
        if ($this->getConfig()->getPaymentConfigData($paymentMethodCode, 'test_flag')) {
            $directFields['ewayTotalAmount'] = $amount;
        }
        else {
            $directFields['ewayTotalAmount'] = round($amount * 100);
        }
        $directFields['ewayCustomerFirstName'] = substr($billingAddress->getFirstname(), 0, 50);
        $directFields['ewayCustomerLastName'] = substr($billingAddress->getLastname(), 0, 50);
        $directFields['ewayCustomerEmail'] = substr($billingAddress->getEmail(), 0, 50);
        $directFields['ewayCustomerAddress'] = substr($billingAddress->getStreet(1), 0, 255);
        $directFields['ewayCustomerPostcode'] = substr($billingAddress->getPostcode(), 0, 6);
        $directFields['ewayCustomerInvoiceDescription'] = substr($this->getConfig()->getOrderDescription($order), 0, 255);
        $directFields['ewayCustomerInvoiceRef'] = substr($order->getIncrementId(), 0, 50);
        $directFields['ewayTrxnNumber'] = substr($order->getIncrementId(), 0, 16);
        $directFields['ewayOption1'] = '';
        $directFields['ewayOption2'] = '';
        $directFields['ewayOption3'] = '';

        // Card holder
        $directFields['ewayCardHoldersName'] = substr($order->getPayment()->getCcOwner(), 0, 50);
        $directFields['ewayCardNumber'] = substr(Mage::helper('core')->decrypt(Mage::getSingleton('core/session')->getCcNumberEnc()), 0, 20);
        $directFields['ewayCardExpiryMonth'] = substr($order->getPayment()->getCcExpMonth(), 0, 2);
        $directFields['ewayCardExpiryYear'] = substr($order->getPayment()->getCcExpYear(), 2, 2);

        // CVN
        if ($this->getConfigData('card_security') != Appmerce_Eway_Model_Config::SECURITY_STANDARD) {
            $directFields['ewayCVN'] = substr(Mage::helper('core')->decrypt(Mage::getSingleton('core/session')->getCcCidEnc()), 0, 4);
        }

        // Beagle Anti-Fraud
        if ($this->getConfigData('card_security') == Appmerce_Eway_Model_Config::SECURITY_BEAGLE) {
            $directFields['ewayCustomerIPAddress'] = substr(Mage::helper('eway')->getRealIpAddr(), 0, 15);
            $directFields['ewayCustomerBillingCountry'] = substr($billingAddress->getCountry(), 0, 2);
        }

        // we don't keep the CC data, never stored in the DB
        // this should help for PCI certification
        // @see $_canSaveCc = false
        Mage::getSingleton('core/session')->setCcNumberEnc(null);
        Mage::getSingleton('core/session')->setCcCidEnc(null);
        Mage::getSingleton('core/session')->getCcOwner(null);
        Mage::getSingleton('core/session')->setCcLast4(null);
        Mage::getSingleton('core/session')->setCcNumber(null);
        Mage::getSingleton('core/session')->setCcCid(null);
        Mage::getSingleton('core/session')->setCcExpMonth(null);
        Mage::getSingleton('core/session')->setCcExpYear(null);
        Mage::getSingleton('core/session')->getCcSsIssue(null);
        Mage::getSingleton('core/session')->setCcSsStartYear(null);
        Mage::getSingleton('core/session')->setCcSsStartMonth(null);

        return $directFields;
    }

    /**
     * Generates array of fields for redirect form
     *
     * @return array
     */
    public function getRefundFields($order)
    {
        if (empty($order)) {
            if (!($order = $this->getOrder())) {
                return array();
            }
        }

        $storeId = $order->getStoreId();
        $paymentMethodCode = $order->getPayment()->getMethod();

        $refundFields = array();
        $refundFields['ewayCustomerID'] = substr($this->getConfigData('customer_id', $storeId), 0, 8);
        $amount = number_format($order->getBaseGrandTotal(), 2, '.', '');
        if ($this->getConfig()->getPaymentConfigData($paymentMethodCode, 'test_flag')) {
            $refundFields['ewayTotalAmount'] = $amount;
        }
        else {
            $refundFields['ewayTotalAmount'] = round($amount * 100);
        }
        $refundFields['ewayCardExpiryMonth'] = substr($order->getPayment()->getCcExpMonth(), 0, 2);
        $refundFields['ewayCardExpiryYear'] = substr($order->getPayment()->getCcExpYear(), 0, 2);
        $refundFields['ewayOriginalTrxnNumber'] = substr($order->getPayment()->getLastTransId(), 0, 16);
        $refundFields['ewayOption1'] = '';
        $refundFields['ewayOption2'] = '';
        $refundFields['ewayOption3'] = '';
        $refundFields['ewayRefundPassword'] = substr($this->getConfigData('refund_password', $storeId), 0, 20);
        $refundFields['ewayCustomerInvoiceRef'] = substr($order->getPayment()->getIncrementId(), 0, 50);

        return $refundFields;
    }

    /**
     * Generates array of token headers for SOAP
     *
     * @return array
     */
    public function getTokenHeaders($order)
    {
        if (empty($order)) {
            if (!($order = $this->getOrder())) {
                return array();
            }
        }

        $storeId = $order->getStoreId();

        $tokenHeaders = array();
        $tokenHeaders['eWAYCustomerID'] = substr($this->getConfigData('customer_id', $storeId), 0, 8);
        $tokenHeaders['Username'] = $this->getConfigData('token_username', $storeId);
        $tokenHeaders['Password'] = $this->getConfigData('token_password', $storeId);

        return $tokenHeaders;
    }

    /**
     * Generates array of token fields for SOAP
     *
     * @return array
     */
    public function getTokenFields($order)
    {
        if (empty($order)) {
            if (!($order = $this->getOrder())) {
                return array();
            }
        }

        $storeId = $order->getStoreId();
        $billingAddress = $order->getBillingAddress();

        $tokenFields = array();
        $tokenFields['Title'] = $this->getConfig()->getGenderCode($order->getCustomerGender());
        $tokenFields['FirstName'] = substr($billingAddress->getFirstname(), 0, 50);
        $tokenFields['LastName'] = substr($billingAddress->getLastname(), 0, 50);
        $tokenFields['Address'] = substr($billingAddress->getStreet(1), 0, 255);
        $tokenFields['State'] = $billingAddress->getRegion();
        $tokenFields['Company'] = $billingAddress->getCompany();
        $tokenFields['PostCode'] = substr($billingAddress->getPostcode(), 0, 6);
        $tokenFields['Country'] = $billingAddress->getCountry();
        $tokenFields['Email'] = substr($billingAddress->getEmail(), 0, 50);
        $tokenFields['Fax'] = $billingAddress->getFax();
        $tokenFields['Phone'] = $billingAddress->getTelephone();
        $tokenFields['CustomerRef'] = $order->getCustomerId();
        $tokenFields['CCNumber'] = substr(Mage::helper('core')->decrypt(Mage::getSingleton('core/session')->getCcNumberEnc()), 0, 20);
        $tokenFields['CCNameOnCard'] = substr($order->getPayment()->getCcOwner(), 0, 50);
        $tokenFields['CCExpiryMonth'] = substr($order->getPayment()->getCcExpMonth(), 0, 2);
        $tokenFields['CCExpiryYear'] = substr($order->getPayment()->getCcExpYear(), 0, 2);

        // we don't keep the CC data, never stored in the DB
        // this should help for PCI certification
        // @see $_canSaveCc = false
        Mage::getSingleton('core/session')->setCcNumberEnc(null);
        Mage::getSingleton('core/session')->setCcCidEnc(null);
        Mage::getSingleton('core/session')->getCcOwner(null);
        Mage::getSingleton('core/session')->setCcLast4(null);
        Mage::getSingleton('core/session')->setCcNumber(null);
        Mage::getSingleton('core/session')->setCcCid(null);
        Mage::getSingleton('core/session')->setCcExpMonth(null);
        Mage::getSingleton('core/session')->setCcExpYear(null);
        Mage::getSingleton('core/session')->getCcSsIssue(null);
        Mage::getSingleton('core/session')->setCcSsStartYear(null);
        Mage::getSingleton('core/session')->setCcSsStartMonth(null);

        return $tokenFields;
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setCcOwner($data->getCcOwner())->setCcLast4(substr($data->getCcNumber(), -4))->setCcNumber($data->getCcNumber())->setCcCid($data->getCcCid())->setCcExpMonth($data->getCcExpMonth())->setCcExpYear($data->getCcExpYear())->setCcSsIssue($data->getCcSsIssue())->setCcSsStartMonth($data->getCcSsStartMonth())->setCcSsStartYear($data->getCcSsStartYear());
        return $this;
    }

    /**
     * Prepare info instance for save
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function prepareSave()
    {
        $info = $this->getInfoInstance();
        Mage::getSingleton('core/session')->setCcCidEnc($info->encrypt($info->getCcCid()));
        Mage::getSingleton('core/session')->setCcNumberEnc($info->encrypt($info->getCcNumber()));
        $info->setCcNumber(null)->setCcCid(null);
        return $this;
    }

}
