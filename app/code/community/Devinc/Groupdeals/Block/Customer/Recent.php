<?php
class Devinc_Groupdeals_Block_Customer_Recent extends Mage_Customer_Block_Account_Dashboard
{	
	//prepare coupon collection
    public function __construct()
    {
        parent::__construct();       
        
        //get all deals order items for logged in customer
		$productIds = Mage::getModel('groupdeals/groupdeals')->getCollection()->getColumnValues('product_id');
		$orderItems = Mage::getResourceModel('sales/order_item_collection')
        	->join('sales/order', 'entity_id=order_id', array('customer_id'=>'customer_id', 'state'=>'state', 'increment_id'=>'increment_id', 'order_status'=>'status'), null,'left')      
            ->addFieldToFilter('product_id', array('in' => $productIds))  
            ->addFieldToFilter('product_type', 'virtual')              
            ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
            ->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
            ->setOrder('created_at', 'desc')
        ;
        
        //create new collection to include coupons that weren't generated yet; coupons are only generated for invoiced orders	
		$couponCollection = new Varien_Data_Collection(); 
        $cnt = 0;
        foreach ($orderItems as $item) {     
			$order = Mage::getModel('sales/order')->load($item->getOrderId());
        	$coupons = Mage::getModel('groupdeals/coupons')->getCollection()->addFieldToFilter('order_item_id', $item->getId())->setOrder('coupon_id', 'desc')->toArray();       	
			$storeId = $order->getStoreId(); 	
			
			for ($i = 0; $i<$item->getQtyOrdered(); $i++) {  	
				$cnt++;			
				$object = new Varien_Object();
        		$object->setId($cnt);
        		$object->setStoreId($storeId);
        		$object->setOrderId($item->getOrderId());
        		$object->setIncrementId($item->getIncrementId());
        		
        		//set coupon name
				$product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($item->getProductId());
				$object->setProduct($product);
				$object->setName($product->getName());
				 
        		$object->setBasePriceInclTax($item->getBasePriceInclTax());
        		$object->setStatus(Mage::getModel('sales/order')->getConfig()->getStatusLabel($item->getOrderStatus()));         		
        		
				//set coupon code/id
				$redeem = $this->__('Not used');
				if ($coupons['items'][$i]['status']=='complete') {
				    $couponCode = $coupons['items'][$i]['coupon_code'];
				    if ($coupons['items'][$i]['redeem']=='used') {
				    	$redeem = $this->__('Used');
				    }
        		    $object->setCouponId($coupons['items'][$i]['coupon_id']);	    
				} else if ($coupons['items'][$i]['status']=='voided') {
				    $couponCode = $this->__('Voided');		    
				} else {
				    $couponCode = $this->__('Coupon not sent');
				}   
        		$object->setCouponCode($couponCode);
        		$object->setRedeem($redeem);
        		
				$couponCollection->addItem($object);
				//if ($cnt!=1 && $cnt!=2 && $cnt!=7) $couponCollection->addItem($object);
				if ($cnt > 4) break;
			}    
			if ($cnt > 4) break;
        }   
             
        $this->setCoupons($couponCollection);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('groupdeals/customer_html_pager', 'coupons.pager')
            ->setCollection($this->getCoupons());
        $this->setChild('pager', $pager);
        $this->getCoupons()->load();
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    
    //get coupon in html format
    public function getCoupon($_coupon, $_order, $_item, $width = '700px',$_mobile = false) {
		$groupdeal = Mage::getModel('groupdeals/groupdeals')->load($_item->getProductId(), 'product_id');
		$customerName = $_order->getBillingAddress()->getName();
		$storeId = $_order->getStoreId();
		if ($_mobile){
			$html = Mage::getModel('groupdeals/coupons')->getMobileCouponHtml($groupdeal, $storeId, $_coupon, $_item, $customerName, $width);
		} else {
			$html = Mage::getModel('groupdeals/coupons')->getCouponHtml($groupdeal, $storeId, $_coupon, $_item, $customerName, $width);
		}
	
		return $html;
    }
}