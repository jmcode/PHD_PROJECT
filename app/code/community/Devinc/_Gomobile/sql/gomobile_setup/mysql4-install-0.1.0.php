<?php

$installer = $this;

$installer->startSetup();

//create home add static block
$staticBlock = array(
                'title' => 'goMobile Home Page',
                'identifier' => 'mobile-home',                    
                'content' => '<div class="home-ad"><img src="{{skin url="images/home_ad.jpg"}}" alt="" /></div>',
                'is_active' => 1,                    
                'stores' => array(0)
                );
Mage::getModel('cms/block')->setData($staticBlock)->save();

$installer->endSetup(); 