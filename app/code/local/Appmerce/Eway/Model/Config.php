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

class Appmerce_Eway_Model_Config extends Mage_Payment_Model_Config
{
    const PAYMENT_SERVICES_PATH = 'payment_services/appmerce_eway/';
    const API_CONTROLLER_PATH = 'eway/api/';
    const DIRECT_CONTROLLER_PATH = 'eway/direct/';
    const RAPID_CONTROLLER_PATH = 'eway/rapid/';

    // Default order statuses
    const DEFAULT_STATUS_PENDING = 'pending';
    const DEFAULT_STATUS_PENDING_PAYMENT = 'pending_payment';
    const DEFAULT_STATUS_PROCESSING = 'processing';

    // Source model Appmerce_Eway_Model_Source_Subscription
    const COUNTRY_AU = 'au';
    const COUNTRY_NZ = 'nz';
    const COUNTRY_UK = 'uk';

    // Source model Appmerce_Eway_Model_Source_Cardsecurity
    const SECURITY_STANDARD = 'standard';
    const SECURITY_CVN = 'cvn';
    const SECURITY_BEAGLE = 'beagle';

    // Source Model Appmerce_Eway_Model_Source_Interfacelanguage
    const LANGUAGE_EN = 'EN';
    const LANGUAGE_ES = 'ES';
    const LANGUAGE_FR = 'FR';
    const LANGUAGE_DE = 'DE';
    const LANGUAGE_NL = 'NL';

    // Local constants
    const TYPE_REDIRECT = 'redirect';
    const TYPE_REFUND = 'refund';
    const TYPE_XML = 'xml';
    const TYPE_TOKEN = 'token';
    const RAPID_LIVE = 'live';
    const RAPID_SANDBOX = 'sandbox';

    /**
     * Get store configuration
     */
    public function getPaymentConfigData($method, $key, $storeId = null)
    {
        return Mage::getStoreConfig('payment/' . $method . '/' . $key, $storeId);
    }

    public function getServiceConfigData($key, $storeId = null)
    {
        return Mage::getStoreConfig(self::PAYMENT_SERVICES_PATH . $key, $storeId);
    }

    /**
     * Return checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Response messages by code
     *
     * @param $code string Response code
     * @return string Untranslated response message
     */
    public function getResponseMessage($responseCode)
    {

        // Transaction response messages
        $transactionResponseMessages = array(
            'CX' => 'Customer Cancelled Transaction',
            '00' => 'Transaction Approved',
            '01' => 'Refer to Issuer',
            '02' => 'Refer to Issuer, Special',
            '03' => 'No Merchant',
            '04' => 'Pick Up Card',
            '05' => 'Do Not Honour',
            '06' => 'Error',
            '07' => 'Pick Up Card, Special',
            '08' => 'Honour With Identification',
            '09' => 'Request In Progress',
            '10' => 'Approved For Partial Amount',
            '11' => 'Approved, VIP',
            '12' => 'Invalid Transaction',
            '13' => 'Invalid Amount',
            '14' => 'Invalid Card Number',
            '15' => 'No Issuer',
            '16' => 'Approved, Update Track 3',
            '19' => 'Re-enter Last Transaction',
            '21' => 'No Action Taken',
            '22' => 'Suspect Malfunction',
            '23' => 'Unacceptable Transaction Fee',
            '25' => 'Unable to Locate Record On File',
            '30' => 'Format Error',
            '31' => 'Bank Not Supported By Switch',
            '33' => 'Expired Card, Capture',
            '34' => 'Suspected Fraud, Retain Card',
            '35' => 'Card Acceptor, Contact Acquirer, Retain Card',
            '36' => 'Restricted Card, Retain Card',
            '37' => 'Contact Acquirer Security Department, Retain Card',
            '38' => 'PIN Tries Exceeded, Capture',
            '39' => 'No Credit Account',
            '40' => 'Function Not Supported',
            '41' => 'Lost Card',
            '42' => 'No Universal Account',
            '43' => 'Stolen Card',
            '44' => 'No Investment Account',
            '51' => 'Insufficient Funds',
            '52' => 'No Cheque Account',
            '53' => 'No Savings Account',
            '54' => 'Expired Card',
            '55' => 'Incorrect PIN',
            '56' => 'No Card Record',
            '57' => 'Function Not Permitted to Cardholder',
            '58' => 'Function Not Permitted to Terminal',
            '59' => 'Suspected Fraud',
            '60' => 'Acceptor Contact Acquirer',
            '61' => 'Exceeds Withdrawal Limit',
            '62' => 'Restricted Card',
            '63' => 'Security Violation',
            '64' => 'Original Amount Incorrect',
            '66' => 'Acceptor Contact ACquirer, Security',
            '67' => 'Capture Card',
            '75' => 'PIN Tries Exceeded',
            '82' => 'CVV Validation Error',
            '90' => 'Cutoff In Progress',
            '91' => 'Card Issuer Unavailable',
            '92' => 'Unable To Route Transaction',
            '93' => 'Cannot Complete, Violation Of The Law',
            '94' => 'Duplicate Transaction',
            '96' => 'System Error',
        );

        // Beagle Fraud Messages
        $fraudResponseMessages = array(
            'F7000' => 'Undefined Fraud Error',
            'F7001' => 'Challenged Fraud',
            'F7002' => 'Country Match Fraud',
            'F7003' => 'High Risk Country Fraud',
            'F7004' => 'Anonymous Proxy Fraud',
            'F7005' => 'Transparent Proxy Fraud',
            'F7006' => 'Free Email Fraud',
            'F7007' => 'International Transaction Fraud',
            'F7008' => 'Risk Score Fraud',
            'F7009' => 'Denied Fraud',
            'F9010' => 'High Risk Billing Country',
            'F9011' => 'High Risk Credit Card Country',
            'F9012' => 'High Risk Customer IP Address',
            'F9013' => 'High Risk Email Address',
            'F9014' => 'High Risk Shipping Country',
            'F9015' => 'Multiple card numbers for single email address',
            'F9016' => 'Multiple card numbers for single location',
            'F9017' => 'Multiple email addresses for single card number',
            'F9018' => 'Multiple email addresses for single location',
            'F9019' => 'Multiple locations for single card number',
            'F9020' => 'Multiple locations for single email address',
            'F9021' => 'Suspicious Customer First Name',
            'F9022' => 'Suspicious Customer Last Name',
            'F9023' => 'Transaction Declined',
            'F9024' => 'Multiple transactions for same address with known credit card',
            'F9025' => 'Multiple transactions for same address with new credit card',
            'F9026' => 'Multiple transactions for same email with new credit card',
            'F9027' => 'Multiple transactions for same email with known credit card',
            'F9028' => 'Multiple transactions for new credit card',
            'F9029' => 'Multiple transactions for known credit card',
            'F9030' => 'Multiple transactions for same email address',
            'F9031' => 'Multiple transactions for same credit card',
            'F9032' => 'Invalid Customer Last Name',
            'F9033' => 'Invalid Billing Street',
            'F9034' => 'Invalid Shipping Street',
        );

        // Validation response messages
        $validationResponseMessages = array(
            'V6000' => 'Validation error',
            'V6001' => 'Invalid CustomerIP',
            'V6002' => 'Invalid DeviceID',
            'V6011' => 'Invalid Payment TotalAmount',
            'V6012' => 'Invalid Payment InvoiceDescription',
            'V6013' => 'Invalid Payment InvoiceNumber',
            'V6014' => 'Invalid Payment InvoiceReference',
            'V6015' => 'Invalid Payment CurrencyCode',
            'V6016' => 'Payment Required',
            'V6017' => 'Payment CurrencyCode Required',
            'V6018' => 'Unknown Payment CurrencyCode',
            'V6021' => 'EWAY_CARDHOLDERNAME Required',
            'V6022' => 'EWAY_CARDNUMBER Required',
            'V6023' => 'EWAY_CARDCVN Required',
            'V6033' => 'Invalid Expiry Date',
            'V6034' => 'Invalid Issue Number',
            'V6035' => 'Invalid Valid From Date',
            'V6040' => 'Invalid TokenCustomerID',
            'V6041' => 'Customer Required',
            'V6042' => 'Customer FirstName Required',
            'V6043' => 'Customer LastName Required',
            'V6044' => 'Customer CountryCode Required',
            'V6045' => 'Customer Title Required',
            'V6046' => 'TokenCustomerID Required',
            'V6047' => 'RedirectURL Required',
            'V6051' => 'Invalid Customer FirstName',
            'V6052' => 'Invalid Customer LastName',
            'V6053' => 'Invalid Customer CountryCode',
            'V6058' => 'Invalid Customer Title',
            'V6059' => 'Invalid RedirectURL',
            'V6060' => 'Invalid TokenCustomerID',
            'V6061' => 'Invalid Customer Reference',
            'V6062' => 'Invalid Customer CompanyName',
            'V6063' => 'Invalid Customer JobDescription',
            'V6064' => 'Invalid Customer Street1',
            'V6065' => 'Invalid Customer Street2',
            'V6066' => 'Invalid Customer City',
            'V6067' => 'Invalid Customer State',
            'V6068' => 'Invalid Customer PostalCode',
            'V6069' => 'Invalid Customer Email',
            'V6070' => 'Invalid Customer Phone',
            'V6071' => 'Invalid Customer Mobile',
            'V6072' => 'Invalid Customer Comments',
            'V6073' => 'Invalid Customer Fax',
            'V6074' => 'Invalid Customer URL',
            'V6075' => 'Invalid ShippingAddress FirstName',
            'V6076' => 'Invalid ShippingAddress LastName',
            'V6077' => 'Invalid ShippingAddress Street1',
            'V6078' => 'Invalid ShippingAddress Street2',
            'V6079' => 'Invalid ShippingAddress City',
            'V6080' => 'Invalid ShippingAddress State',
            'V6081' => 'Invalid ShippingAddress PostalCode',
            'V6082' => 'Invalid ShippingAddress Email',
            'V6083' => 'Invalid ShippingAddress Phone',
            'V6084' => 'Invalid ShippingAddress Country',
            'V6085' => 'Invalid ShippingAddress ShippingMethod',
            'V6086' => 'Invalid ShippingAddress Fax',
            'V6091' => 'Unknown Customer CountryCode',
            'V6092' => 'Unknown ShippingAddress CountryCode',
            'V6100' => 'Invalid EWAY_CARDNAME',
            'V6101' => 'Invalid EWAY_CARDEXPIRYMONTH',
            'V6102' => 'Invalid EWAY_CARDEXPIRYYEAR',
            'V6103' => 'Invalid EWAY_CARDSTARTMONTH',
            'V6104' => 'Invalid EWAY_CARDSTARTYEAR',
            'V6105' => 'Invalid EWAY_CARDISSUENUMBER',
            'V6106' => 'Invalid EWAY_CARDCVN',
            'V6107' => 'Invalid EWAY_ACCESSCODE',
            'V6108' => 'Invalid CustomerHostAddress',
            'V6109' => 'Invalid UserAgent',
            'V6110' => 'Invalid EWAY_CARDNUMBER',
        );

        // Rapid API 3.0 response codes
        if (strlen($responseCode) > 2) {
            $firstLetter = substr($responseCode, 0, 1);
            switch ($firstLetter) {
                case 'A' :
                case 'D' :
                    $responseCode = substr($responseCode, 3, 2);
                    $responseMessages = $transactionResponseMessages;
                    break;

                case 'F' :
                    $responseMessages = $fraudResponseMessages;
                    break;

                case 'V' :
                    $responseMessages = $validationResponseMessages;

                    // In case of validation error, show frontend message:
                    $errorMessage = array_key_exists($responseCode, $responseMessages) ? $responseMessages[$responseCode] : 'Error message could not be determined';
                    $this->getCheckout()->addError($errorMessage);
                    break;

                default :
                    $responseMessages = $transactionResponseMessages;
            }
        }
        else {
            $responseMessages = $transactionResponseMessages;
        }

        $errorMessage = array_key_exists($responseCode, $responseMessages) ? $responseMessages[$responseCode] : 'Error message could not be determined';
        return $errorMessage;
    }

    /**
     * Return gateways
     */
    public function getGateways()
    {
        return array(
            self::COUNTRY_AU => array(
                self::TYPE_XML => array(
                    self::SECURITY_STANDARD => array(
                        'test' => 'https://www.eway.com.au/gateway/xmltest/testpage.asp',
                        'live' => 'https://www.eway.com.au/gateway/xmlpayment.asp',
                    ),
                    self::SECURITY_CVN => array(
                        'test' => 'https://www.eway.com.au/gateway_cvn/xmltest/testpage.asp',
                        'live' => 'https://www.eway.com.au/gateway_cvn/xmlpayment.asp',
                    ),
                    self::SECURITY_BEAGLE => array(
                        'test' => 'https://www.eway.com.au/gateway_cvn/xmltest/testpage.asp',
                        'live' => 'https://www.eway.com.au/gateway_cvn/xmlbeagle.asp',
                    ),
                ),
                self::TYPE_TOKEN => array(
                    'test' => 'https://www.eway.com.au/gateway/ManagedPaymentService/test/managedCreditCardPayment.asmx?wsdl',
                    'live' => 'https://www.eway.com.au/gateway/ManagedPaymentService/managedCreditCardPayment.asmx?wsdl',
                ),
                self::TYPE_REDIRECT => array(
                    'request' => 'https://au.ewaygateway.com/Request/',
                    'result' => 'https://au.ewaygateway.com/Result/',
                ),
                self::TYPE_REFUND => array('live' => 'https://www.eway.com.au/gateway/xmlpaymentrefund.asp'),
            ),
            self::COUNTRY_NZ => array(
                self::TYPE_XML => array(
                    self::SECURITY_STANDARD => array(
                        'test' => 'https://www.eway.co.nz/gateway/xmltest/testpage.asp',
                        'live' => 'https://www.eway.co.nz/gateway/xmlpayment.asp',
                    ),
                    self::SECURITY_CVN => array(
                        'test' => 'https://www.eway.co.nz/gateway_cvn/xmltest/testpage.asp',
                        'live' => 'https://www.eway.co.nz/gateway_cvn/xmlpayment.asp',
                    ),
                    self::SECURITY_BEAGLE => array(
                        'test' => 'https://www.eway.co.nz/gateway_cvn/xmltest/testpage.asp',
                        'live' => 'https://www.eway.co.nz/gateway_cvn/xmlbeagle.asp',
                    ),
                ),
                self::TYPE_TOKEN => array(
                    'test' => 'https://www.eway.co.nz/gateway/ManagedPaymentService/test/managedCreditCardPayment.asmx?wsdl',
                    'live' => 'https://www.eway.co.nz/gateway/ManagedPaymentService/managedCreditCardPayment.asmx?wsdl',
                ),
                self::TYPE_REDIRECT => array(
                    'request' => 'https://nz.ewaygateway.com/Request/',
                    'result' => 'https://nz.ewaygateway.com/Result/',
                ),
                self::TYPE_REFUND => array('live' => 'https://www.eway.co.nz/gateway/xmlpaymentrefund.asp'),
            ),
            self::COUNTRY_UK => array(self::TYPE_REDIRECT => array(
                    'request' => 'https://payment.ewaygateway.com/Request/',
                    'result' => 'https://payment.ewaygateway.com/Result/',
                )),
        );
    }

    /**
     * Return Rapid 3.0 gateways
     */
    public function getRapidGateways()
    {
        return array(
            self::RAPID_LIVE => array(
                'soap' => 'https://api.ewaypayments.com/soap.asmx?wsdl',
                'rest' => 'https://api.ewaypayments.com/AccessCodes',
                'http' => array(
                    'xml' => 'https://api.ewaypayments.com/CreateAccessCode.xml',
                    'json' => 'https://api.ewaypayments.com/CreateAccessCode.json',
                ),
                'rpc' => array(
                    'xml' => 'https://api.ewaypayments.com/xml-rpc',
                    'json' => 'https://api.ewaypayments.com/json-rpc',
                ),
            ),
            self::RAPID_SANDBOX => array(
                'soap' => 'https://api.sandbox.ewaypayments.com/soap.asmx?wsdl',
                'rest' => 'https://api.sandbox.ewaypayments.com/AccessCodes',
                'http' => array(
                    'xml' => 'https://api.sandbox.ewaypayments.com/CreateAccessCode.xml',
                    'json' => 'https://api.sandbox.ewaypayments.com/CreateAccessCode.json',
                ),
                'rpc' => array(
                    'xml' => 'https://api.sandbox.ewaypayments.com/xml-rpc',
                    'json' => 'https://api.sandbox.ewaypayments.com/json-rpc',
                ),
            ),
        );
    }

    /**
     * GetSoapHeaderUrls
     */
    public function getSoapHeaderUrls()
    {
        return array(self::TYPE_TOKEN => array(
                self::COUNTRY_AU => 'https://www.eway.com.au/gateway/managedpayment',
                self::COUNTRY_NZ => 'https://www.eway.co.nz/gateway/managedpayment',
            ), );
    }

    /**
     * Translate Magento gender codes to eWAY text
     */
    public function getGenderCode($magento_code)
    {
        $magento_genders = array(
            '123' => 'Mr.',
            '124' => 'Mrs.',
        );
        return array_key_exists($magento_code, $magento_genders) ? $magento_genders[$magento_code] : 'Mrs.';
    }

    /**
     * Config helper functions
     */
    public function getModifiableDetails($bool)
    {
        return $bool ? 'True' : 'False';
    }

    /**
     * Return order description
     *
     * @param Mage_Sales_Model_Order
     * @return string
     */
    public function getOrderDescription($order)
    {
        return Mage::helper('eway')->__('Order %s', $order->getIncrementId());
    }

    /**
     * Get order statuses
     */
    public function getOrderStatus($code)
    {
        $status = $this->getPaymentConfigData($code, 'order_status');
        if (empty($status)) {
            $status = self::DEFAULT_STATUS_PENDING;
        }
        return $status;
    }

    public function getPendingStatus($code)
    {
        $status = $this->getPaymentConfigData($code, 'pending_status');
        if (empty($status)) {
            $status = self::DEFAULT_STATUS_PENDING_PAYMENT;
        }
        return $status;
    }

    public function getProcessingStatus($code)
    {
        $status = $this->getPaymentConfigData($code, 'processing_status');
        if (empty($status)) {
            $status = self::DEFAULT_STATUS_PROCESSING;
        }
        return $status;
    }

    /**
     * Return URLs
     */
    public function getApiUrl($key, $storeId = null)
    {
        return Mage::getUrl(self::API_CONTROLLER_PATH . $key, array(
            '_store' => $storeId,
            '_secure' => true
        ));
    }

    public function getDirectUrl($key, $storeId = null)
    {
        return Mage::getUrl(self::DIRECT_CONTROLLER_PATH . $key, array(
            '_store' => $storeId,
            '_secure' => true
        ));
    }

    public function getRapidUrl($key, $storeId = null)
    {
        return Mage::getUrl(self::RAPID_CONTROLLER_PATH . $key, array(
            '_store' => $storeId,
            '_secure' => true
        ));
    }

}
