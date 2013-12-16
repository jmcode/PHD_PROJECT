<?php
require_once('app/Mage.php');
Mage::app();
$allStores=Mage::app()->getStores();
$current_date = Mage::getModel('core/date')->date('d F Y');

$orders = Mage::getModel('sales/order')
->getCollection()
->addAttributeToSelect("*");
//->addAttributeToFilter('store_id',$_storeId)'
 //'processing', 'processed', 'pending fullfilment'
 echo count($orders);
if(count($orders)<1)
{echo "<p style='color:red'>Order's not found for this store</p>";
}
foreach($orders as $order)
	{
		
		echo $_shippingAddress=$order->getShippingAddress();
		
		if($_shippingAddress->getRegion()=='Auckland'){
		//echo "<br><b>". $_shippingAddress->getRegion()."</b>";
			echo "<br>Order No".$order_id=$order->getIncrementId();
			$items = $order->getAllItems();
			
			echo "<br>itemcount".$itemcount=count($items);
			foreach ($items as $itemId => $item)
			{	$cust_options= $item->getProductOptions();
				if($cust_options['options'])
				{
					foreach ($cust_options['options'] as $cust_opt){
					$counter=0;
							$customdate =$cust_opt['value'];
							echo "<br>Item Name-".$item_name = $item->getName();
							}		
							
				}
			echo "<br>".$customdate;
						$cdate='22 February 2013';
			if($customdate== $cdate){
						echo "<br>Item Name-".$item_name = $item->getName();
						$counter+=1;
			echo "co".$counter;
			}
			
			}
		
		}
		if($_shippingAddress->getRegion()=='Hamilton'){
		
		
		
		}
		if($_shippingAddress->getRegion()=='Wellington'){
		
		
		}
		if($_shippingAddress->getRegion()=='Tauranga'){
		
		
		}
		if($_shippingAddress->getRegion()==''){
		
		echo "<br>Region field is blank";
		}
		
}
	/* 	echo "<p style ='color:green'>Order No".$order_id=$order->getIncrementId(). "</p>";
		$items = $order->getAllItems();
		
		$itemcount=count($items);
		foreach ($items as $itemId => $item)
		{	$cust_options= $item->getProductOptions();
		
		echo "<br>Item Name-".$item_name = $item->getName();
		//print_r($cust_options['options']);
		if($cust_options['options']){
			foreach ($cust_options['options'] as $cust_opt){
			//echo "fvgd".$item_name .="-".$cust_opt['value'];
			//echo "do".$item_name .="-".$cust_opt['label'];
			//=='datre;{}
			echo $item_name .="-".$cust_opt['value'];
			echo "current_date" .$current_date;
			$cdate='04 February 2013';
			//$date = new DateTime('2013-02-01');
			if($cust_opt['value']== $cdate)
				{echo "daysless";
				
				$collection = Mage::getResourceModel('customer/customer_collection')
               ->addNameToSelect("*")
                ->addAttributeToSelect('cdate')
               ->joinAttribute('shipping_region', 'customer_address/region', 'default_shipping', null, 'left');
                           
			foreach($collection as $collect)
			   {echo $collect->shipping_region;
			   }

				
				}
			
			}
        }		
		echo "<br>price-".$price=$item->getPrice();
		echo "<br>Sku-".$sku['item_sku']=$item->getSku();
		echo "<br>ItemId-".$itemid=$item->getProductId();
		echo "<br>date-".$itemid=$item->getDelDate();
		echo "<br>ItemQty-".$itemqty=$item->getQtyToInvoice()."<br>";
		
		
		//$deliveryDate = $order->helper('orderid')->getFormatedDeliveryDate($order->getShippingArrivalDate());
		echo $orderDate ;
		}
			
	}
} */

/* $items=array(array('Auckland', 32,8,8,8), 
			array('Hamilton' ,12,4,4,4),
			array('Tauranga', 12,4,4,4 ),
			array('Wellington',24,6,6,6));
			$height=$page->getHeight();
			$width=$page->getWidth();
$counter=1;
$items_count=count($items);

$page->drawLine(70, ($page->getHeight()-110), ($width-70), ($page->getHeight()-110));
$page->drawLine(($width-70), ($page->getHeight()-110), ($width-70), ($page->getHeight()-198));
$page->drawLine(($width-160), ($page->getHeight()-110), ($width-160), ($page->getHeight()-198));
$page->drawLine(($width-250), ($page->getHeight()-110), ($width-250), ($page->getHeight()-198));
$page->drawLine(($width-330), ($page->getHeight()-110), ($width-330), ($page->getHeight()-198));
$page->drawLine(($width-415), ($page->getHeight()-110), ($width-415), ($page->getHeight()-198));
$page->drawLine(($width-525), ($page->getHeight()-110), ($width-525), ($page->getHeight()-198));

//$page->drawLine(70, ($page->getHeight()-110), 70, ($page->getHeight()-230));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 10);
$page->drawText('Region',80,$page->getHeight()-123);
$page->drawtext('Pure Green',210,($page->getHeight()-123));
$page->drawText('Yellow Hit',290,($page->getHeight()-123));
$page->drawText('Zesty Lemonade',360,($page->getHeight()-123));
$page->drawText('Cashew Dream',450,($page->getHeight()-123));
//$page->drawLine(70, ($page->getHeight()-130), 460, ($page->getHeight()-130));
//$page->drawLine(155, ($page->getHeight()-110), 155, ($page->getHeight()-230));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 12);
for($i=0;$i<=$items_count;$i++)
{
$page->drawText($items[$i][0],80,($height-140*$counter));
$page->drawtext($items[$i][1],250,($height-140*$counter));
$page->drawtext($items[$i][2],330,($height-140*$counter));
$page->drawtext($items[$i][3],420,($height-140*$counter));
$page->drawtext($items[$i][4],500,($height-140*$counter));
$page->drawLine(70,(($height-140*$counter)+12),($width-70),(($height-140*$counter)+12));

$counter+=0.1;
}
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 12);
$page->drawText('Total',80,($page->getHeight()-195));
$page->drawtext('80',250,($page->getHeight()-195));
$page->drawtext('22',330,($page->getHeight()-195));
$page->drawtext('22',420,($page->getHeight()-195));
$page->drawtext('22',500,($page->getHeight()-195));
$page->drawLine(70,(($height-140*$counter)+12),($width-70),(($height-140*$counter)+12));
$pdf->render();
$pdf->save('orderpdf/Production Volumes.pdf'); */
?>