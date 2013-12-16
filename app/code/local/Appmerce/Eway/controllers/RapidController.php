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

class Appmerce_Eway_RapidController extends Appmerce_Eway_Controller_Common
{
    /**
     * Return payment API model
     *
     * @return Appmerce_Eway_Model_Api_Rapid
     */
    protected function getApi()
    {
        return Mage::getSingleton('eway/api_rapid');
    }

    /**
     * Render placement form and set New Order Status
     *
     * @see eway/rapid/placement
     */
    public function placementAction()
    {
        $this->saveCheckoutSession();
        $order = $this->getLastRealOrder();
        if ($order->getId()) {
            $this->rapidProcess($order);
        }
        else {
            $this->_redirect('checkout/cart', array('_secure' => true));
        }
    }

    /**
     * Rapid Process
     */
    public function rapidProcess($order)
    {
        $accessFields = $this->getApi()->getAccessFields($order);

        // Build SOAP request
        $soapClient = new SoapClient($this->getApi()->getRapidGatewayUrl('soap'), array(
            'trace' => 1,
            'authentication' => SOAP_AUTHENTICATION_BASIC,
            'login' => $this->getApi()->getConfigData('api_key'),
            'password' => $this->getApi()->getConfigData('rapid_password'),
        ));

        // SOAP Response
        try {
            $response = $soapClient->CreateAccessCode(array('request' => $accessFields));
        }
        catch (SoapFault $e) {
            Mage::throwException($e->faultstring);
        }

        // Debug
        if ($this->getApi()->getConfigData('debug_flag')) {
            $url = $this->getRequest()->getPathInfo();
            Mage::getModel('eway/api_debug')->setDir('out')->setUrl($url)->setData('data', print_r($accessFields, true))->save();
            Mage::getModel('eway/api_debug')->setDir('in')->setUrl($url)->setData('data', print_r($response, true))->save();
        }

        // If we have a valid Access Code, we do a Curl Post for the
        // Card Data we already collected (we're a step ahead of Rapid API)
        $response = $response->CreateAccessCodeResult;
        if (isset($response->AccessCode) && !empty($response->AccessCode)) {

            // Handle Token Payments
            if (isset($response->Customer->TokenCustomerID)) {
                $billingAgreementId = $this->getApi()->getBillingAgreementId($order);
                if (!$billingAgreementId || $billingAgreementId != $response->Customer->TokenCustomerID) {
                    $this->createBillingAgreement($order, $response->Customer->TokenCustomerID);
                }

                $this->getProcess()->pending($order, $note, $transactionId, 1, true);
                $this->_redirect('checkout/onepage/success', array('_secure' => true));
            }
            else {
                $accessCode = (string)$response->AccessCode;
                $this->getCheckout()->setEwayRapidFormData($this->getApi()->getCardFields($order, $accessCode));
                $this->getCheckout()->setEwayRapidFormAction($response->FormActionURL);
                $this->loadLayout();
                $this->renderLayout();
                return;
            }
        }
        else {
            $note = $this->buildNote($response, (string)$response->Errors);
            $this->getProcess()->cancel($order, $note, 0, 1, true);
            $this->_redirect('checkout/cart', array('_secure' => true));
        }
    }

    /**
     * Update billing agreement
     */
    public function updateBillingAgreement($order, $billingAgreementId)
    {
        $storeId = $order->getStoreId();
        $customerId = $order->getCustomerId();
        $methodCode = $order->getPayment()->getMethod();

        $db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::getSingleton('core/resource')->getTableName('sales_billing_agreement');
        $db->query('UPDATE ' . $tableName . ' SET status = "canceled", updated_at = NOW() WHERE store_id = "' . $storeId . '" AND method_code = "' . $methodCode . '" AND customer_id = "' . $customerId . '"');
        $db->query('INSERT INTO ' . $tableName . ' (customer_id, method_code, reference_id, status, store_id, agreement_label) VALUES ("' . $customerId . '", "' . $methodCode . '", "' . $billingAgreementId . '", "active", "' . $storeId . '", "' . Mage::helper('eway')->__('eWAY Billing Agreement') . '")');
    }

    /**
     * Redirect Action
     * /eway/rapid/redirect
     */
    public function redirectAction()
    {
        $accessCode = $this->getRequest()->getParam('AccessCode');
        if ($accessCode) {
            $resultFields = $this->getApi()->getResultFields($accessCode);

            // Build SOAP request
            $soapClient = new SoapClient($this->getApi()->getRapidGatewayUrl('soap'), array(
                'trace' => 1,
                'authentication' => SOAP_AUTHENTICATION_BASIC,
                'login' => $this->getApi()->getConfigData('api_key'),
                'password' => $this->getApi()->getConfigData('rapid_password'),
            ));

            // SOAP Response
            try {
                $response = $soapClient->GetAccessCodeResult(array('request' => $resultFields));
            }
            catch (SoapFault $e) {
                Mage::throwException($e->faultstring);
            }

            // Debug
            if ($this->getApi()->getConfigData('debug_flag')) {
                $url = $this->getRequest()->getPathInfo();
                Mage::getModel('eway/api_debug')->setDir('out')->setUrl($url)->setData('data', print_r($resultFields, true))->save();
                Mage::getModel('eway/api_debug')->setDir('in')->setUrl($url)->setData('data', print_r($response, true))->save();
            }

            // Process the final access response!
            $response = $response->GetAccessCodeResultResult;
            $transactionId = (string)$response->TransactionID;
            $order = Mage::getModel('sales/order')->loadByIncrementId($response->InvoiceNumber);

            // Build response note for backend
            $fraud = 0;
            $note = $this->buildNote($response, (string)$response->ResponseCode, $fraud);

            // Switch response
            switch ((string)$response->TransactionStatus) {
                case 1 :
                    $this->getProcess()->success($order, $note, $transactionId, 1, true);
                    $this->_redirect('checkout/onepage/success', array('_secure' => true));
                    break;

                default :
                    $this->getProcess()->cancel($order, $note, $transactionId, 1, true);
                    $this->_redirect('checkout/cart', array('_secure' => true));
            }
        }
    }

    /**
     * Build $note with error message
     */
    public function buildNote($response, $codes, &$fraud = FALSE)
    {
        $note = '';
        $responseCodes = explode(',', $codes);

        // Main response message
        if (isset($response->ResponseMessage)) {
            $note .= Mage::helper('eway')->__('Response Message: %s', (string)$response->ResponseMessage);
        }

        // Error messages
        foreach ($responseCodes as $code) {
            if (substr($code, 0, 1) == 'F') {
                ++$fraud;
            }
            $responseMessage = $this->getApi()->getConfig()->getResponseMessage($code);
            $note .= '<br /> - ' . Mage::helper('eway')->__('%s (%s).', $responseMessage, $code);
        }

        // Beagle score
        if (isset($response->BeagleScore)) {
            $note .= '<br />' . Mage::helper('eway')->__('Beagle Score: %s', (string)$response->BeagleScore);
        }

        return $note;
    }

}
