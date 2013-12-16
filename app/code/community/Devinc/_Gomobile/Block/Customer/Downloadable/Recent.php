<?php
class Devinc_Gomobile_Block_Customer_Downloadable_Recent extends Mage_Downloadable_Block_Customer_Products_List
{

	public function __construct()
    {
        parent::__construct();
		$items = $this->getItems();
        
		$items->setPageSize(5);
		
    }

}
