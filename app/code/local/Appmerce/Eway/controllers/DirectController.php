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

class Appmerce_Eway_DirectController extends Appmerce_Eway_Controller_Common
{
    /**
     * Return payment API model
     *
     * @return Appmerce_Eway_Model_Api_Direct
     */
    protected function getApi()
    {
        return Mage::getSingleton('eway/api_direct');
    }

    /**
     * Render placement form and set New Order Status
     *
     * @see eway/direct/placement
     */
    public function placementAction()
    {
        $this->saveCheckoutSession();
        $order = $this->getLastRealOrder();
        if ($order->getId()) {
            if ($this->getApi()->getConfigData('token_payments')) {
                $this->tokenProcess($order);
            }
            else {
                $this->directProcess($order);
            }
        }
        else {
            $this->_redirect('checkout/cart', array('_secure' => true));
        }
    }

    /**
     * Token Process
     */
    public function tokenProcess($order)
    {
        $tokenHeaders = $this->getApi()->getTokenHeaders($order);
        $tokenFields = $this->getApi()->getTokenFields($order);

        // Build SOAP request
        $soapType = Appmerce_Eway_Model_Config::TYPE_TOKEN;
        $soapClient = new SoapClient($this->getApi()->getGatewayUrl($soapType), array('trace' => 1));
        $soapHeaders = new SoapHeader($this->getApi()->getSoapHeaderUrl($soapType), 'eWAYHeader', $tokenHeaders);
        $soapClient->__setSoapHeaders(array($soapHeaders));

        // SOAP Response
        try {
            $response = $soapClient->CreateCustomer($tokenFields, $soapHeaders);
        }
        catch (SoapFault $e) {
            Mage::throwException($e->faultstring);
        }

        // Debug
        if ($this->getApi()->getConfigData('debug_flag')) {
            $url = $this->getRequest()->getPathInfo();
            Mage::getModel('eway/api_debug')->setDir('out')->setUrl($url)->setData('data', print_r($tokenFields, true))->save();
            Mage::getModel('eway/api_debug')->setDir('in')->setUrl($url)->setData('data', print_r($response, true))->save();
        }

        // Process
        if (isset($response->CreateCustomerResult)) {
            $managedAlias = (string)$response->CreateCustomerResult;
            $order->getPayment()->setEwayManagedAlias($managedAlias);

            $note = Mage::helper('eway')->__('Token payment authorized. Please create a Magento invoice and capture the amount online.');
            $note .= '<br />' . Mage::helper('eway')->__('Managed Customer ID: %s.', $managedAlias);
            $this->getProcess()->pending($order, $note, 0, 1, true);
            $this->_redirect('checkout/onepage/success', array('_secure' => true));
        }
        else {
            $note = Mage::helper('eway')->__('Token payment failed, payment canceled.');
            $this->getProcess()->cancel($order, $note, 0, 1, true);
            $this->_redirect('checkout/cart', array('_secure' => true));
        }
    }

    /**
     * Direct Process
     */
    public function directProcess($order)
    {
        // Build gateway URL and $directFields XML message
        $directFields = $this->getApi()->getDirectFields($order);
        $xmlMessage = $this->getApi()->createXmlMessage($directFields);

        // Send request, receive response
        $type = Appmerce_Eway_Model_Config::TYPE_XML;
        $security = $this->getApi()->getConfigData('card_security');
        $gateway = $this->getApi()->getGatewayUrl($type, $security);
        $request = $this->getApi()->curlPostXml($gateway, $xmlMessage);
        $response = new SimpleXMLElement($request);

        // Debug
        if ($this->getApi()->getConfigData('debug_flag')) {
            $url = $this->getRequest()->getPathInfo();
            Mage::getModel('eway/api_debug')->setDir('out')->setUrl($url)->setData('data', print_r($directFields, true))->save();
            Mage::getModel('eway/api_debug')->setDir('in')->setUrl($url)->setData('data', print_r($request, true))->save();
        }

        $transactionId = (string)$response->ewayTrxnNumber;
        $responseCode = substr((string)$response->ewayTrxnError, 0, 2);
        $note = $this->getApi()->getConfig()->getResponseMessage($responseCode);

        // Switch response
        switch ((string)$response->ewayTrxnStatus) {
            case Appmerce_Eway_Model_Api::STATUS_TRUE :
                $this->getProcess()->success($order, $note, $transactionId, 1, true);
                $this->_redirect('checkout/onepage/success', array('_secure' => true));
                break;

            case Appmerce_Eway_Model_Api::STATUS_FALSE :
            default :
                $this->getProcess()->cancel($order, $note, $transactionId, 1, true);
                $this->_redirect('checkout/cart', array('_secure' => true));
        }
    }

}
