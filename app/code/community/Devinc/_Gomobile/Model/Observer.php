<?php

class Devinc_Gomobile_Model_Observer extends Mage_Core_Model_Config
{		
	//disable any custom modules
    public function hookToControllerActionPreDispatch($observer)
    {
		$enabled = false;
		$storeId = Mage::app()->getStore()->getId();
		$isModuleEnabled = Mage::getStoreConfig('advanced/modules_disable_output/Devinc_Gomobile', $storeId);
		$isEnabled = Mage::getStoreConfig('gomobile/configuration/enabled', $storeId);
		if ($isModuleEnabled == 0 && $isEnabled == 1) $enabled = true;		
		
		$model = Mage::getSingleton('Mage_Customer_Model_Session');
		$switchTo = $model->getSwitchTo();
		
		if ($enabled && Mage::getDesign()->getArea() != 'adminhtml' && !$this->_isAdminFrontNameMatched($observer->getEvent()->getFront()->getRequest()) && ($switchTo=='mobile' || ($switchTo!='desktop' && Mage::getModel('license/module')->isMobile()))) {
			//clean previous config cache and Mage cache(i.e. registries) to not cause registry issues
			Mage::app()->getCacheInstance()->flush();
				
			//disable config cache to not save gomobile specific configuration
			$cache = Mage::app()->getCacheInstance();
			$cache->banUse('config');
			
			//get all allowed modules aside from Mage default modules
			$extendGomobile = explode(',', Mage::getStoreConfig('gomobile/configuration/modules'));
			$allowedModules = array_merge(array('Phoenix_Moneybookers', 'Devinc_License', 'Devinc_Gomobile', 'Devinc_Dailydeal', 'Devinc_Multipledeals', 'Devinc_Groupdeals'), $extendGomobile);

	        //disable module events
			$moduleFiles = $this->_getDeclaredModuleFiles();
	        foreach ($moduleFiles as $file) {
				$moduleFile = new Mage_Core_Model_Config_Base();
	            $moduleFile->loadFile($file);               
	            foreach ($moduleFile->getNode('modules')->children() as $moduleName => $moduleNode) {
		            if (!in_array($moduleName,$allowedModules) && substr($moduleName,0,5)!="Mage_") {		 
						$config = Mage::getModuleDir('etc', $moduleName).DS.'config.xml';	
						$configFile = new Mage_Core_Model_Config_Base();
			            $configFile->loadFile($config);  	 
			            if(!$configFile->getNode('global')) { continue; }
						foreach ($configFile->getNode('global') as $global) {							
						    foreach ($global->children() as $nodeName => $node) {
						    	if ($nodeName=='events') {
						    	    foreach($node as $eventName => $eventConfig) {
						    	        if(!$eventConfig->observers) { continue; }
						    		    foreach($eventConfig->observers[0] as $observerName => $observer) {
						    		        if ($observer->class && $observer->class!='') {
												$nodePath = 'global/'.$nodeName.'/'.$eventName.'/observers/'.$observerName.'/type';
						    		        	Mage::getConfig()->setNode($nodePath, 'disabled', true);
						    				}
						    		    }
						    	    }
						    	} 
						    }
						}
		            } elseif (!in_array($moduleName, $allowedModules)) {
			            $allowedModules[] = $moduleName;
		            }
		        }
	        }	

			//disable modules
			if (!empty($allowedModules)) {
	            Mage::getConfig()->addAllowedModules($allowedModules);
	        }
	
			//reinit config
			Mage::getConfig()->reinit();
	    }
    }
    
    /**
     * Check if requested path starts with one of the admin front names
     *
     * @param Zend_Controller_Request_Http $request
     * @return boolean
     */
    protected function _isAdminFrontNameMatched($request)
    {
        $useCustomAdminPath = (bool)(string)Mage::getConfig()
            ->getNode(Mage_Adminhtml_Helper_Data::XML_PATH_USE_CUSTOM_ADMIN_PATH);
        $customAdminPath = (string)Mage::getConfig()->getNode(Mage_Adminhtml_Helper_Data::XML_PATH_CUSTOM_ADMIN_PATH);
        $adminPath = ($useCustomAdminPath) ? $customAdminPath : null;

        if (!$adminPath) {
            $adminPath = (string)Mage::getConfig()
                ->getNode(Mage_Adminhtml_Helper_Data::XML_PATH_ADMINHTML_ROUTER_FRONTNAME);
        }
        $adminFrontNames = array($adminPath);

        // Check for other modules that can use admin router (a lot of Magento extensions do that)
        $adminFrontNameNodes = Mage::getConfig()->getNode('admin/routers')
            ->xpath('*[not(self::adminhtml) and use = "admin"]/args/frontName');

        if (is_array($adminFrontNameNodes)) {
            foreach ($adminFrontNameNodes as $frontNameNode) {
                /** @var $frontNameNode SimpleXMLElement */
                array_push($adminFrontNames, (string)$frontNameNode);
            }
        }

        $pathPrefix = ltrim($request->getPathInfo(), '/');
        $urlDelimiterPos = strpos($pathPrefix, '/');
        if ($urlDelimiterPos) {
            $pathPrefix = substr($pathPrefix, 0, $urlDelimiterPos);
        }

        return in_array($pathPrefix, $adminFrontNames);
    }
    
    public function setCityRegion($observer) {	
    	if (Mage::helper('core')->isModuleEnabled('Devinc_Groupdeals')) {
    		$helper = Mage::helper('groupdeals');
    		if ($helper->isEnabled() && $helper->getCity()=='') {
    			$version = Mage::helper('gomobile')->getGroupdealsVersion();
    			$storeId = Mage::app()->getStore()->getId();
    			$defaultCity = Mage::getStoreConfig('groupdeals/configuration/homepage_deals', $storeId);
    			
    			if ($version>=133) {
	    			if ($defaultCity!='default') {
			    		$crc = Mage::getModel('groupdeals/crc')->getCollection()->addFieldToFilter('city', $defaultCity)->getFirstItem();
		    		}
	    		
	    			if ($defaultCity!='default' && $crc->getId()) {
						$helper->setCity($defaultCity);		
						$helper->setRegion($crc->getRegion());
					} else {
						$crc = Mage::getModel('groupdeals/crc')->getCollection()->setOrder('crc_id', 'DESC')->getFirstItem();
						if ($city = $crc->getCity()) {
							$helper->setCity($city);	
							$helper->setRegion($crc->getRegion());			
						}
					}
				} else {
					if ($defaultCity!='default') {
			    		$groupdeal = Mage::getModel('groupdeals/groupdeals')->getCollection()->addFieldToFilter('city', $defaultCity)->getFirstItem();
		    		}
	    		
	    			if ($defaultCity!='default' && $groupdeal->getId()) {
						$helper->setCity($defaultCity);		
					} else {
						$groupdeal = Mage::getModel('groupdeals/groupdeals')->getCollection()->setOrder('groupdeals_id', 'DESC')->getFirstItem();
						if ($city = $groupdeal->getCity()) {
							$helper->setCity($city);			
						}
					}
				}
			}
		}
    }

}