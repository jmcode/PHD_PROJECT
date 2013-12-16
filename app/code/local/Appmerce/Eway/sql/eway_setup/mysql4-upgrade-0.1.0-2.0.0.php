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

$installer = $this;
/* @var $installer Mage_Core_Model_Mysql4_Setup */

$installer->startSetup();

/**
 * Upgrade configuration from Morningtime to Appmerce
 * 
 * @note Before this update will run it is required to delete 'buckaroo_setup'
 * from core_resource
 */
$installer->run("

UPDATE `{$this->getTable('core/config_data')}` 
    SET path = REPLACE(path, 'payment_services/morningtime_buckaroo', 'payment_services/appmerce_buckaroo')
    WHERE path LIKE '%payment_services/morningtime_buckaroo%';

");

$installer->endSetup();
