<?php
class MW_Mcore_Model_Observer
{	
	public function adloginsuccess($o)
	{		 	
		Mage::getSingleton('core/session')->unsNotification();				
		Mage::helper('mcore')->updatestatus();
	}	
	
	public function logoutupdate($o){
		Mage::getSingleton('core/session')->unsNotification();	
		Mage::helper('mcore')->updatestatus();
		Mage::helper('mcore')->resetSpecNotification();	
	}
	
	public function reloadAdmin($o)
	{    
		Mage::getSingleton('core/session')->unsNotification();			
	}
	
	public function updateStatus()
	{
		Mage::helper('mcore')->getServerNotification();
		Mage::helper('mcore')->updatestatus();	
	}
	
}