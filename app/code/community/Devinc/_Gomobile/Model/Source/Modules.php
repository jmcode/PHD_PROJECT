<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
class Devinc_Gomobile_Model_Source_Modules extends Mage_Core_Model_Config
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {		
		$moduleFiles = $this->_getDeclaredModuleFiles();
		$allowedModules = array('Phoenix_Moneybookers', 'Devinc_License', 'Devinc_Gomobile', 'Devinc_Dailydeal', 'Devinc_Multipledeals', 'Devinc_Groupdeals', 'Devinc_Gdtheme');
		
		$modules[0]['value'] = '';
		$modules[0]['label'] = '';
		
		$i = 1;
	    foreach ($moduleFiles as $file) {
		    $moduleFile = new Mage_Core_Model_Config_Base();
	        $moduleFile->loadFile($file);               
	        foreach ($moduleFile->getNode('modules')->children() as $moduleName => $moduleNode) {
		        if (!in_array($moduleName,$allowedModules) && substr($moduleName,0,5)!="Mage_" && $moduleName!='depends') {
					$modules[$i]['value'] = $moduleName;
					
					$dependsArray = array();
					if ($moduleNode->depends) {
		                foreach ($moduleNode->depends->children() as $depend) {
		                	if (substr($depend->getName(),0,5)!="Mage_") {
			                    $dependsArray[] = $depend->getName();
			                }
		                }
		            }
		            
		            $depends = '';
		            if (!empty($dependsArray)) {
			            $depends = ' (requires '.implode(',', $dependsArray).')';
		            }
		            
					$modules[$i]['label'] = $moduleName.$depends;
					$i++;
		        }
		    }
	    }	
		
        return $modules;
    }

}
