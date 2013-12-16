<?php
class Devinc_Gomobile_IndexController extends Mage_Checkout_Controller_Action 
{	         
    public function catalogAction()
    {
		$enabled = false;
		$isModuleEnabled = Mage::getStoreConfig('advanced/modules_disable_output/Devinc_Gomobile', $storeId);
		$isEnabled = Mage::getStoreConfig('gomobile/configuration/enabled', $storeId);
		if ($isModuleEnabled == 0 && $isEnabled == 1) $enabled = true;
		
		if ($enabled && (Mage::getSingleton("customer/session")->getSwitchTo()=='mobile' || (Mage::getSingleton("customer/session")->getSwitchTo()!='desktop' && Mage::getModel('license/module')->isMobile()))) {	
			$this->loadLayout();		
			$this->renderLayout();      
		} else {
	    	$this->_redirect(''); 
		}      
    }   
          
    public function switchToDesktopAction()
    {
    	Mage::getSingleton("customer/session")->setSwitchTo('desktop');
    	
    	$currentUrl = $this->getRequest()->getParam('current_url', false);
    	if ($currentUrl) {
	    	$this->_redirectUrl(rawurldecode($currentUrl));    	
    	} else {
	    	$this->_redirect('');     	
    	}        
    }   
          
    public function switchToMobileAction()
    {
    	Mage::getSingleton("customer/session")->setSwitchTo('mobile');
    	
    	$currentUrl = $this->getRequest()->getParam('current_url', false);
    	if ($currentUrl) {
	    	$this->_redirectUrl(rawurldecode($currentUrl));     	
    	} else {
	    	$this->_redirect('');     	
    	}    	
    	         
    } 

}