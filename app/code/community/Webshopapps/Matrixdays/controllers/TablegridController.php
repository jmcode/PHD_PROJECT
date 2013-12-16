<?php
class Webshopapps_Matrixdays_TablegridController extends Mage_Core_Controller_Front_Action
{

	private $_rates;
    protected $_address;

	/**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote = null;

    protected $_checkoutSession;


    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError() //|| $this->getOnepage()->getQuote()->getIsMultiShipping()
            ) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))) {
            $this->_ajaxRedirectResponse();
            return true;
        }

        return false;
    }

    public function getDayRateAction() {
    	if ($this->_expireAjax()) {
            return;
        }
        $addressId=null;
        
        if ($this->getRequest()->isGet()) {
          $selectedDate = $this->getRequest()->getParam('date');
          $addressId 	= $this->getRequest()->getParam('address_id');
        }
        
        if (!is_null($addressId)) {
        	$this->_address = $this->getQuote()->getAddressById($addressId);
        }
        
    	if (empty($this->_rates)) {
        	$this->_rates = $this->getShippingRates();
        }
        $resultSet='';
    	foreach ($this->_rates as $code => $rates) {
        	if ($code != 'matrixdays') {
        		continue;
        	}
        	foreach ($rates as $rate) {
        		if($selectedDate!=$rate->getExpectedDelivery()) {
        			continue;
        		}
        		$resultSet[$rate->getCode()] = array(
        			//'code' 			=> ,
        			'price' 				=> $this->getShippingPrice($rate->getPrice(), Mage::helper('tax')->displayShippingPriceIncludingTax()),
        		//	'method_title' 			=> $rate->getMethodTitle(),
        			'method_description' 	=> $rate->getMethodDescription(),
        		);
        	}
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($resultSet));

    }

    protected function getAddress()
    {
		if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }
        return $this->_address;
    }


  	protected function getShippingPrice($price, $flag)
    {
        return $this->getQuote()->getStore()->convertPrice(Mage::helper('tax')->getShippingPrice($price, $flag, $this->getAddress()), true);
    }


    /**
     * This should be in model really
     */
    protected function getShippingRates()
    {
        $address = $this->getAddress();

        if (empty($this->_rates)) {
            $address->collectShippingRates()->save();

            $groups = $address->getGroupedAllShippingRates();

            return $this->_rates = $groups;
        }

        return $this->_rates;
    }


    /**
     * Get frontend checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckout()
    {
     	if ($this->_quote === null) {
        	$this->_checkoutSession = Mage::getSingleton('checkout/session');
    	}
        return $this->_checkoutSession;
    }

    protected function getQuote()
    {
        if ($this->_quote === null) {
            return $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }

   protected function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

}