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

class Appmerce_Eway_Model_Api_Rapid extends Appmerce_Eway_Model_Api implements Mage_Payment_Model_Billing_Agreement_MethodInterface
{
    protected $_code = 'eway_rapid';
    protected $_formBlockType = 'eway/form_rapid';

    // Magento features
    protected $_canSaveCc = false;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canCreateBillingAgreement = true;
    protected $_canManageBillingAgreements = true;

    // Actions
    const ACTION_PROCESS_PAYMENT = 'ProcessPayment';
    const ACTION_TOKEN_PAYMENT = 'TokenPayment';

    // Response Modes
    const RESPONSE_MODE_REDIRECT = 'Redirect';
    const RESPONSE_MODE_RETURN = 'Return';

    /**
     * Init billing agreement
     *
     * @param Mage_Payment_Model_Billing_AgreementAbstract $agreement
     * @return Mage_Paypal_Model_Method_Agreement
     */
    public function initBillingAgreementToken(Mage_Payment_Model_Billing_AgreementAbstract $agreement)
    {
        return $agreement;
    }

    /**
     * Retrieve billing agreement customer details by token
     *
     * @param Mage_Payment_Model_Billing_AgreementAbstract $agreement
     * @return array
     */
    public function getBillingAgreementTokenInfo(Mage_Payment_Model_Billing_AgreementAbstract $agreement)
    {
        return $agreement;
    }

    /**
     * Create billing agreement by token specified in request
     *
     * @param Mage_Payment_Model_Billing_AgreementAbstract $agreement
     * @return Mage_Paypal_Model_Method_Agreement
     */
    public function placeBillingAgreement(Mage_Payment_Model_Billing_AgreementAbstract $agreement)
    {
        return $agreement;
    }

    /**
     * Update billing agreement status
     *
     * @param Mage_Payment_Model_Billing_AgreementAbstract $agreement
     * @return Mage_Paypal_Model_Method_Agreement
     */
    public function updateBillingAgreementStatus(Mage_Payment_Model_Billing_AgreementAbstract $agreement)
    {
        return $agreement;
    }

    /**
     * Check capture availability
     *
     * @return bool
     */
    public function canCapture()
    {
        if (!$this->getConfigData('allow_billing_agreement_wizard')) {
            $this->_canCapture = false;
        }
        return $this->_canCapture;
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
                $accessFields = $this->getAccessFields($order);

                // Build SOAP request
                $soapClient = new SoapClient($this->getRapidGatewayUrl('soap'), array(
                    'trace' => 1,
                    'authentication' => SOAP_AUTHENTICATION_BASIC,
                    'login' => $this->getConfigData('api_key'),
                    'password' => $this->getConfigData('rapid_password'),
                ));

                // SOAP Response
                try {
                    $response = $soapClient->CreateAccessCode(array('request' => $accessFields));
                }
                catch (SoapFault $e) {
                    Mage::throwException($e->faultstring);
                }

                // Debug
                if ($this->getConfigData('debug_flag')) {
                    $url = $this->getRequest()->getPathInfo();
                    Mage::getModel('eway/api_debug')->setDir('out')->setUrl($url)->setData('data', print_r($accessFields, true))->save();
                    Mage::getModel('eway/api_debug')->setDir('in')->setUrl($url)->setData('data', print_r($response, true))->save();
                }

                // If we have a valid Access Code, we do a Curl Post for the
                // Card Data we already collected (we're a step ahead of Rapid API)
                $response = $response->CreateAccessCodeResult;
                if (isset($response->AccessCode) && !empty($response->AccessCode)) {
                    $accessCode = (string)$response->AccessCode;
                    $cardFields = http_build_query($this->getCardFields($order, $accessCode), '', '&');
                    $response = $this->curlPostRedirect($response->FormActionURL, $cardFields);
                    $this->captureProcess($accessCode[1]);
                }
                else {
                    $note = Mage::helper('eway')->__('Could not reach Rapid API endpoint. Errors: %s', $response->Errors);
                    $this->getProcess()->cancel($order, $note, 0, 1, true);
                    $this->_redirect('checkout/cart', array('_secure' => true));
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
     * Return Process
     */
    public function captureProcess($accessCode = false)
    {
        if ($accessCode) {
            $resultFields = $this->getResultFields($accessCode);

            // Build SOAP request
            $soapClient = new SoapClient($this->getRapidGatewayUrl('soap'), array(
                'trace' => 1,
                'authentication' => SOAP_AUTHENTICATION_BASIC,
                'login' => $this->getConfigData('api_key'),
                'password' => $this->getConfigData('rapid_password'),
            ));

            // SOAP Response
            try {
                $response = $soapClient->GetAccessCodeResult(array('request' => $resultFields));
            }
            catch (SoapFault $e) {
                Mage::throwException($e->faultstring);
            }

            // Debug
            if ($this->getConfigData('debug_flag')) {
                $url = $this->getRequest()->getPathInfo();
                Mage::getModel('eway/api_debug')->setDir('out')->setUrl($url)->setData('data', print_r($resultFields, true))->save();
                Mage::getModel('eway/api_debug')->setDir('in')->setUrl($url)->setData('data', print_r($response, true))->save();
            }

            // Process the final access response!
            $response = $response->GetAccessCodeResultResult;
            $transactionId = (string)$response->TransactionID;
            $responseMessage = $this->getConfig()->getResponseMessage((string)$response->ResponseCode);
            $note = Mage::helper('eway')->__('eWAY Rapid Status: %s (%s).', $responseMessage, (string)$response->ResponseCode);
            $order = Mage::getModel('sales/order')->loadByIncrementId($response->InvoiceNumber);

            // Switch response
            switch ((string)$response->TransactionStatus) {
                case true :
                    $order->getPayment()->setAppmerceResponseCode((string)$response->ResponseCode);
                    $order->getPayment()->setTransactionId($transactionId);
                    $order->getPayment()->setLastTransId($transactionId);
                    $order->save();
                    break;

                case false :
                    Mage::throwException(Mage::helper('eway')->__('eWAY capture failed: %s', $note));
                    break;

                default :
            }
        }
    }

    /**
     * Get redirect URL after placing order
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->getConfig()->getRapidUrl('placement');
    }

    /**
     * Generates array of fields for redirect form
     *
     * @return array
     */
    public function getAccessFields($order)
    {
        if (empty($order)) {
            if (!($order = $this->getOrder())) {
                return array();
            }
        }

        $storeId = $order->getStoreId();
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress || !is_object($shippingAddress)) {
            $shippingAddress = $billingAddress;
        }
        $paymentMethodCode = $order->getPayment()->getMethod();

        $accessFields = array();

        // Basics
        $accessFields['RedirectUrl'] = $this->getConfig()->getRapidUrl('redirect', $storeId);
        $accessFields['IPAddress'] = Mage::helper('eway')->getRealIpAddr();
        $accessFields['Method'] = self::ACTION_PROCESS_PAYMENT;

        // Payment
        $accessFields['Payment']['TotalAmount'] = number_format($order->getBaseGrandTotal(), 2, '.', '') * 100;
        $accessFields['Payment']['InvoiceNumber'] = substr($order->getIncrementId(), 0, 64);
        $accessFields['Payment']['InvoiceDescription'] = substr($this->getConfig()->getOrderDescription($order), 0, 64);
        $accessFields['Payment']['InvoiceReference'] = substr($order->getIncrementId(), 0, 64);

        // Customer
        $accessFields['Customer']['Reference'] = substr($order->getCustomerId(), 0, 50);
        $accessFields['Customer']['Title'] = $this->getConfig()->getGenderCode($order->getCustomerGender());
        $accessFields['Customer']['FirstName'] = substr($billingAddress->getFirstname(), 0, 50);
        $accessFields['Customer']['LastName'] = substr($billingAddress->getLastname(), 0, 50);
        $accessFields['Customer']['CompanyName'] = substr($billingAddress->getCompany(), 0, 50);
        $accessFields['Customer']['Street1'] = substr($billingAddress->getStreet(1), 0, 50);
        $accessFields['Customer']['Street2'] = substr($billingAddress->getStreet(2), 0, 50);
        $accessFields['Customer']['City'] = substr($billingAddress->getCity(), 0, 50);
        $accessFields['Customer']['State'] = substr($billingAddress->getState(), 0, 50);
        $accessFields['Customer']['PostalCode'] = substr($billingAddress->getPostcode(), 0, 30);
        $accessFields['Customer']['Country'] = strtolower(substr($billingAddress->getCountry(), 0, 2));
        $accessFields['Customer']['Email'] = substr($billingAddress->getEmail(), 0, 50);

        // Phone & fax cause trouble if they are not valid,...
        // $accessFields['Customer']['Phone'] = substr($billingAddress->getTelephone(), 0, 50);
        // $accessFields['Customer']['Fax'] = substr($billingAddress->getFax(), 0, 50);

        // Line items,...
        // @todo implement line items (+shipping and +-discount)
        // $accessFields['Items']['LineItem']['SKU'] = '';
        // $accessFields['Items']['LineItem']['Description'] = '';
        // $accessFields['Items']['LineItem']['Quantity'] = '';
        // $accessFields['Items']['LineItem']['UnitCost'] = '';
        // $accessFields['Items']['LineItem']['Tax'] = '';
        // $accessFields['Items']['LineItem']['Total'] = '';

        // Shipping address
        // $accessFields['ShippingAddress']['ShippingMethod'] = '';
        $accessFields['ShippingAddress']['FirstName'] = substr($shippingAddress->getFirstname(), 0, 50);
        $accessFields['ShippingAddress']['LastName'] = substr($shippingAddress->getLastname(), 0, 50);
        $accessFields['ShippingAddress']['Street1'] = substr($shippingAddress->getStreet(1), 0, 50);
        $accessFields['ShippingAddress']['Street2'] = substr($shippingAddress->getStreet(2), 0, 50);
        $accessFields['ShippingAddress']['City'] = substr($shippingAddress->getCity(), 0, 50);
        $accessFields['ShippingAddress']['State'] = substr($shippingAddress->getState(), 0, 50);
        $accessFields['ShippingAddress']['PostalCode'] = substr($shippingAddress->getPostcode(), 0, 30);
        $accessFields['ShippingAddress']['Country'] = strtolower(substr($shippingAddress->getCountry(), 0, 2));
        $accessFields['ShippingAddress']['Email'] = substr($shippingAddress->getEmail(), 0, 50);
        
        // Phone & fax cause trouble if they are not valid,...
        // $accessFields['ShippingAddress']['Fax'] = substr($shippingAddress->getFax(), 0, 50);
        // $accessFields['ShippingAddress']['Phone'] = substr($shippingAddress->getTelephone(), 0, 50);

        // Custom Fields
        // $accessFields['Options']['OptionName']['Value'] = substr('', 0, 255);

        // Token Payments
        if ($this->getConfigData('allow_billing_agreement_wizard')) {
            $billingAgreementId = $this->getBillingAgreementId($order);
            if ($billingAgreementId) {
                $accessFields['Customer']['TokenCustomerID'] = $billingAgreementId;
            }
            $accessFields['Method'] = self::ACTION_TOKEN_PAYMENT;
        }

        return $accessFields;
    }

    /**
     * Get billing agreement
     */
    public function getBillingAgreementId($order)
    {
        $storeId = $order->getStoreId();
        $customerId = $order->getCustomerId();
        $methodCode = $order->getPayment()->getMethod();

        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName = Mage::getSingleton('core/resource')->getTableName('sales_billing_agreement');
        $result = $db->fetchOne('SELECT reference_id FROM ' . $tableName . ' WHERE customer_id = "' . $customerId . '" AND store_id = "' . $storeId . '" AND method_code = "' . $methodCode . '" AND status = "active"');

        return $result;
    }

    /**
     * Generates array of token fields for SOAP
     *
     * @return array
     */
    public function getCardFields($order, $accessCode, $response = false)
    {
        if (empty($order)) {
            if (!($order = $this->getOrder())) {
                return array();
            }
        }

        $cardFields = array();
        $cardFields['EWAY_ACCESSCODE'] = $accessCode;

        // Handle Token Payments
        if ($this->getConfigData('allow_billing_agreement_wizard') && $response) {
            $cardFields['EWAY_CARDNAME'] = $response->Customer->CardName;
            $cardFields['EWAY_CARDNUMBER'] = $response->Customer->CardNumber;
            $cardFields['EWAY_CARDEXPIRYMONTH'] = $response->Customer->CardExpiryMonth;
            $cardFields['EWAY_CARDEXPIRYYEAR'] = $response->Customer->CardExpiryYear;
        }
        else {

            // Expiration date mm/yy
            $month = $order->getPayment()->getCcExpMonth();
            $mm = (string)$month < 10 ? '0' . $month : $month;
            $yy = substr((string)$order->getPayment()->getCcExpYear(), 2, 2);

            $cardFields['EWAY_CARDNAME'] = $order->getPayment()->getCcOwner();
            $cardFields['EWAY_CARDNUMBER'] = Mage::helper('core')->decrypt(Mage::getSingleton('core/session')->getCcNumberEnc());
            $cardFields['EWAY_CARDEXPIRYMONTH'] = $mm;
            $cardFields['EWAY_CARDEXPIRYYEAR'] = $yy;
            $cardFields['EWAY_CARDCVN'] = Mage::helper('core')->decrypt(Mage::getSingleton('core/session')->getCcCidEnc());

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
        }

        return $cardFields;
    }

    /**
     * Post with CURL and return response
     *
     * @param $postUrl The URL with ?key=value
     * @param $postData string Message
     * @return reponse XML Object
     */
    public function curlPostRedirect($postUrl, $postData = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postUrl);

        // We are redirected, but want to read that URL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        if ($postData) {
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, "$postData");
        }

        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        curl_close($ch);

        return $header;
    }

    /**
     * Get gateway Url
     *
     * @return string
     */
    public function getRapidGatewayUrl($type, $security = false, $mode = false)
    {
        $gateways = $this->getConfig()->getRapidGateways();
        $test = $this->getConfigData('test_flag') ? 'sandbox' : 'live';

        switch ($type) {
            case 'soap' :
            case 'rest' :
                $url = $gateways[$test][$type];
                break;

            case 'http' :
            case 'rpc' :
                $url = $gateways[$test][$type][$security];
                break;

            default :
        }
        return $url;
    }

    /**
     * Generates array of token fields for SOAP
     *
     * @return array
     */
    public function getResultFields($accessCode)
    {
        $resultFields = array();
        $resultFields['AccessCode'] = $accessCode;
        return $resultFields;
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
