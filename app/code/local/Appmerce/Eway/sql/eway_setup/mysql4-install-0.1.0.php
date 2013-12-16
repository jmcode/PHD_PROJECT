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
/* @var $installer Appmerce_Eway_Model_Mysql4_Setup */

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('eway/api_debug')}`;
CREATE TABLE `{$this->getTable('eway/api_debug')}` (
  `debug_id` int(10) unsigned NOT null auto_increment,
  `debug_at` timestamp NOT null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `dir` enum('in', 'out'),
  `url` varchar(255),
  `data` text,
  PRIMARY KEY  (`debug_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();

$installer->addAttribute('order_payment', 'eway_managed_alias', array());
$installer->addAttribute('order_payment', 'appmerce_response_code', array('type' => 'int'));
