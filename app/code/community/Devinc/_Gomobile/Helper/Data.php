<?php

class Devinc_Gomobile_Helper_Data extends Mage_Core_Helper_Abstract
{	
	public function getSwitchToDesktopUrl()
	{	
		return 'javascript:window.location = \''.$this->_getUrl('gomobile/index/switchToDesktop').'?current_url=\'+encodeURIComponent(window.location.href);';
	}
	
	public function getSwitchToMobileUrl()
	{		
		return 'javascript:window.location = \''.$this->_getUrl('gomobile/index/switchToMobile').'?current_url=\'+encodeURIComponent(window.location.href);';
	}
	
	public function getGroupdealsVersion() {
		return (int)substr(str_replace('.','',Mage::getConfig()->getNode()->modules->Devinc_Groupdeals->version),1,100);
	}
	
	public function getCategoryFilter() {
		$groupDealsVersion = (int)str_replace(".", "", Mage::getConfig()->getModuleConfig("Devinc_Groupdeals")->version);
    	if (Mage::helper('core')->isModuleEnabled('Devinc_Groupdeals') && Mage::helper('groupdeals')->isEnabled() && $groupDealsVersion>=133) {
    		return $this->getLayout()->createBlock('groupdeals/layer_view')->setBlockId('category_filter')->setTemplate('groupdeals/layer/view.phtml')->toHtml();
    	}
    	
    	return '';
	}

}