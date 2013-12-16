<?php 
require_once('app/Mage.php');
Mage::app();

if(isset($_GET['upflag']) && $_GET['upflag']=='1'){
Mage::getModel('core/cookie')->set('upflag_enable', 1, 0, '/');
echo "tt".Mage::getModel('core/cookie')->get('upflag_enable');
}
else if(isset($_GET['upflag']) && $_GET['upflag']=='0'){
Mage::getModel('core/cookie')->set('upflag_enable', 0, 0, '/');
} ?>
