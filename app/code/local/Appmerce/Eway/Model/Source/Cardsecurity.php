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

class Appmerce_Eway_Model_Source_Cardsecurity
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Appmerce_Eway_Model_Config::SECURITY_STANDARD,
                'label' => Mage::helper('eway')->__('Standard'),
            ),
            array(
                'value' => Appmerce_Eway_Model_Config::SECURITY_CVN,
                'label' => Mage::helper('eway')->__('Enable CVN'),
            ),
            array(
                'value' => Appmerce_Eway_Model_Config::SECURITY_BEAGLE,
                'label' => Mage::helper('eway')->__('Enable CVN + Beagle Anti-Fraud'),
            ),
        );
    }

}
