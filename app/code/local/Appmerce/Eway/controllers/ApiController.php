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

class Appmerce_Eway_ApiController extends Appmerce_Eway_Controller_Common
{
    /**
     * Return payment API model
     *
     * @return Appmerce_Eway_Model_Api_Redirect
     */
    protected function getApi()
    {
        return Mage::getSingleton('eway/api_redirect');
    }

    /**
     * Render placement form
     *
     * @see eway/api/placement
     */
    public function placementAction()
    {
        $this->saveCheckoutSession();
        $order = $this->getLastRealOrder();
        if ($order->getId()) {

            // Build gateway URL and formFields string
            $redirectFields = $this->getApi()->getRedirectFields($order);
            $query = http_build_query($redirectFields, '', '&');
            $query = str_replace(' ', '%21', $query);

            // Send request, receive response
            $type = Appmerce_Eway_Model_Config::TYPE_REDIRECT;
            $gateway = $this->getApi()->getGatewayUrl($type, false, 'request');
            $request = $this->getApi()->curlPostXml($gateway . '?' . $query);
            $response = new SimpleXMLElement($request);

            // Debug
            if ($this->getApi()->getConfigData('debug_flag')) {
                $url = $this->getRequest()->getPathInfo();
                Mage::getModel('eway/api_debug')->setDir('out')->setUrl($url)->setData('data', print_r($redirectFields, true))->save();
                Mage::getModel('eway/api_debug')->setDir('in')->setUrl($url)->setData('data', $request)->save();
            }

            // Switch response
            switch ($response->Result) {
                case Appmerce_Eway_Model_Api::STATUS_TRUE :
                    $this->_redirectUrl($response->URI);
                    break;

                case Appmerce_Eway_Model_Api::STATUS_FALSE :
                    $errorMessage = (string)$response->Error;
                    $this->getCheckout()->addError($errorMessage);
                    $this->_redirect('checkout/cart', array('_secure' => true));
                    break;

                default :
            }
        }
        else {
            $this->_redirect('checkout/cart', array('_secure' => true));
        }
    }

    /**
     * Success action
     *
     * @see eway/api/return
     */
    public function returnAction()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['AccessPaymentCode'])) {
            $resultFields = $this->getApi()->getResultFields($params);
            $query = http_build_query($resultFields, '', '&');
            $query = str_replace(' ', '%21', $query);

            // Send request, receive response
            $type = Appmerce_Eway_Model_Config::TYPE_REDIRECT;
            $gateway = $this->getApi()->getGatewayUrl($type, false, 'result');
            $request = $this->getApi()->curlPostXml($gateway . '?' . $query);
            $response = new SimpleXMLElement($request);

            // Debug
            if ($this->getApi()->getConfigData('debug_flag')) {
                $url = $this->getRequest()->getPathInfo();
                Mage::getModel('eway/api_debug')->setDir('out')->setUrl($url)->setData('data', print_r($resultFields, true))->save();
                Mage::getModel('eway/api_debug')->setDir('in')->setUrl($url)->setData('data', $request)->save();
            }

            $order = Mage::getModel('sales/order')->loadByIncrementId((string)$response->MerchantInvoice);
            $note = $this->getApi()->getConfig()->getResponseMessage((string)$response->ResponseCode);
            $transactionId = (string)$response->AuthCode;

            // Switch response by status or code
            if (isset($response->TransactionStatus)) {
                switch ((string)$response->TransactionStatus) {
                    case 'true' :
                        $this->getProcess()->success($order, $note, $transactionId, 1, true);
                        $this->_redirect('checkout/onepage/success', array('_secure' => true));
                        break;

                    default :
                        $this->getProcess()->cancel($order, $note, $transactionId, 1, true);
                        $this->_redirect('checkout/cart', array('_secure' => true));
                }
            }
            else {
                switch ((string)$response->ResponseCode) {
                    case '00' :
                    case '08' :
                    case '10' :
                    case '11' :
                    case '16' :
                        $this->getProcess()->success($order, $note, $transactionId, 1, true);
                        $this->_redirect('checkout/onepage/success', array('_secure' => true));
                        break;

                    default :
                        $this->getProcess()->cancel($order, $note, $transactionId, 1, true);
                        $this->_redirect('checkout/cart', array('_secure' => true));
                }
            }
        }
        else {
            $this->getProcess()->repeat();
            $this->_redirect('checkout/cart', array('_secure' => true));
        }
    }

    /**
     * Error action
     *
     * @see eway/api/error
     */
    public function cancelAction()
    {
        $this->getProcess()->repeat();
        $this->_redirect('checkout/cart', array('_secure' => true));
    }

}
