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

class Appmerce_Eway_Model_Api extends Mage_Payment_Model_Method_Abstract
{
    protected $_formBlockType = 'eway/form';
    protected $_infoBlockType = 'eway/info';

    // Magento features
    protected $_isGateway = false;
    protected $_canOrder = false;
    protected $_canAuthorize = false;
    protected $_canCapture = false;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_isInitializeNeeded = true;
    protected $_canFetchTransactionInfo = false;
    protected $_canReviewPayment = false;
    protected $_canCreateBillingAgreement = false;
    protected $_canManageRecurringProfiles = false;

    // Restrictions
    protected $_allowCurrencyCode = array(
        'GBP',
        'AUD',
        'NZD',
        'USD',
    );

    // Response codes
    const STATUS_TRUE = 'True';
    const STATUS_FALSE = 'False';

    public function __construct()
    {
        $this->_config = Mage::getSingleton('eway/config');
        return $this;
    }

    /**
     * Return configuration instance
     *
     * @return Appmerce_Eway_Model_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Validate if payment is possible
     *  - check allowed currency codes
     *
     * @return bool
     */
    public function validate()
    {
        parent::validate();
        $currency_code = $this->getCurrencyCode();
        if (!empty($this->_allowCurrencyCode) && !in_array($currency_code, $this->_allowCurrencyCode)) {
            $errorMessage = Mage::helper('eway')->__('Selected currency (%s) is not compatible with this payment method.', $currency_code);
            Mage::throwException($errorMessage);
        }
        return $this;
    }

    /**
     * Get gateway Url
     *
     * @return string
     */
    public function getGatewayUrl($type, $security = false, $mode = false)
    {
        $subscription = $this->getConfigData('subscription');
        $gateways = $this->getConfig()->getGateways();
        $test = $this->getConfigData('test_flag') ? 'test' : 'live';

        switch ($type) {
            case Appmerce_Eway_Model_Config::TYPE_XML :
                $url = $gateways[$subscription][$type][$security][$test];
                break;

            case Appmerce_Eway_Model_Config::TYPE_REDIRECT :
                $url = $gateways[$subscription][$type][$mode];
                break;

            case Appmerce_Eway_Model_Config::TYPE_TOKEN :
                $url = $gateways[$subscription][$type][$test];
                break;

            case Appmerce_Eway_Model_Config::TYPE_REFUND :
                $url = $gateways[$subscription][$type]['live'];
                break;

            case Appmerce_Eway_Model_Config::TYPE_RAPID :
                $url = $gateways[$subscription][$type][$security];
                break;

            default :
        }
        return $url;
    }

    /**
     * Get gateway Url
     *
     * @return string
     */
    public function getSoapHeaderUrl($type)
    {
        $subscription = $this->getConfigData('subscription');
        $gateways = $this->getConfig()->getSoapHeaderUrls();
        return $url = $gateways[$type][$subscription];
    }

    /**
     * Post with CURL and return response
     *
     * @param $postUrl The URL with ?key=value
     * @param $postXml string XML message
     * @return reponse XML Object
     */
    public function curlPostXml($postUrl, $postXML = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($postXML) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, "$postXML");
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * Post with CURL and return response
     *
     * @param $postUrl The URL with ?key=value
     * @param $postData string Message
     * @return reponse XML Object
     */
    public function curlPost($postUrl, $postData = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($postData) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, "$postData");
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * Create XML Message
     *
     * @param $fields array
     * @return string
     */
    public function createXmlMessage($fields)
    {
        $xmlMessage = '';
        $xmlMessage .= '<?xml version="1.0" ?>' . "\n";
        $xmlMessage .= '<ewaygateway>' . "\n";
        foreach ($fields as $key => $value) {
            $xmlMessage .= '<' . $key . '>' . $value . '</' . $key . '>' . "\n";
        }
        $xmlMessage .= '</ewaygateway>' . "\n";
        return $xmlMessage;
    }

    /**
     * Decide currency code type
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return Mage::app()->getStore()->getBaseCurrencyCode();
    }

}
