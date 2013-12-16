<?php
require_once('app/Mage.php');
Mage::app();
include('lib/Zend/Pdf.php');
$pdf = new Zend_Pdf();
$pdf->pages[] = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
$page=$pdf->pages[0]; // this will get reference to the first page.
$style = new Zend_Pdf_Style();
$style->setLineColor(new Zend_Pdf_Color_Rgb(0,0,0));
$page->setStyle($style);
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 14);
$page->drawText('Production Volumes:30 Jan 2013',180,($page->getHeight()-90));


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

$oneday=array();$threeday=array();$fiveeday=array();
$sumone=0;$sumthree=0;$sumfive=0;
$sumadone=0;$sumadthree=0;$sumadfive=0;
$adoneday=array();$adthreeday=array();$adfiveeday=array();
$onedayhami=array();$threedayhami=array();$fiveedayhami=array();


foreach($orders as $order)
	{  // $qtyarray=array();
		
		echo $_shippingAddress=$order->getShippingAddress();
		//echo "<pre>"; print_r($_shippingAddress) ;
		if ($_shippingAddress)
		{
		if($_shippingAddress->getRegion()=='Auckland')
		{
		$cone=0;
		$cthree=0;
		$cfive=0;
		$cadone=0;$cadthree=0;$cadfive=0;
			echo "<br><b>". $_shippingAddress->getRegion()."</b>";
			echo "<br>Order No".$order_id=$order->getIncrementId();
			$items = $order->getAllItems();
			echo "<br>itemcount".$itemcount=count($items);
			foreach ($items as $itemId => $item)
			{	$cust_options= $item->getProductOptions();
					if($cust_options['options'])
				{
					foreach ($cust_options['options'] as $cust_opt)
							{
							$counter=0;
							$customdate =$cust_opt['value'];
							echo "<br>Item Name-".$item_name = $item->getName();
							}		
				}
				$cdate='11 February 2013';
				if($customdate== $cdate)
				{
					if(!$item->getParentItemId() && $item->getProductType()=='bundle')
					{}else{
					//if($item->getProductType()!='bundle')
					echo "<br>".$customdate;
					$ordereditem=$item_name = $item->getName();
					echo "<br>Item ordered Name-".$item_name = $item->getName();
					$qty=$item->getqtyInvoiced();
					echo "Invoice qty=". $item->getqtyInvoiced();
					$counter+=1;
					echo "co".$counter;	
					if($item->getParentItemId())
					{
					if(($item_name=="onedaycleanse"))
					{
						$cone=$cone+1;
						echo "no of onedaycleanse ". $cone;
						$oneday[]=$item->getqtyInvoiced();
					}
					if(($item_name=="threedaycleanse"))
					{
						echo "<br> part";
						$cthree=$cthree+1;
						echo "noo of threedaycleanse ". $cthree;
						$threeday[]=$item->getqtyInvoiced();
						print_r($threeday);
					}
					if(($item_name=="fivedaycleanse"))					
					{
						$cfive=$cfive+1;
						echo "noo of fivedaycleanse ". $cfive;
						$fiveday[]=$item->getqtyInvoiced();
					}
					if(($item_name=="advanceonedaycleanse"))
					{
						$cadone=$cadone+1;
						echo "no of advanceonedaycleanse ". $cadone;
						$adoneday[]=$item->getqtyInvoiced();
					}
					if(($item_name=="advancethreedaycleanse"))
					{
						echo "<br> part";
						$cadthree=$cadthree+1;
						echo "noo of advancethreedaycleanse ". $cadthree;
						$adthreeday[]=$item->getqtyInvoiced();
						print_r($adthreeday);
					}
					if(($item_name=="advancefivedaycleanse"))					
					{
						$cadfive=$cadfive+1;
						echo "noo of advancefivedaycleanse ". $cadfive;
						$adfiveday[]=$item->getqtyInvoiced();
					}
					}
					}
				}
			}
		}
		
		if($_shippingAddress->getRegion()=='Hamilton')
		{
		$cone=0;
		$cthree=0;
		$cfive=0;
			echo "<br><b>". $_shippingAddress->getRegion()."</b>";
			echo "<br>Order No".$order_id=$order->getIncrementId();
			$items = $order->getAllItems();
			echo "<br>itemcount".$itemcount=count($items);
			foreach ($items as $itemId => $item)
			{	$cust_options= $item->getProductOptions();
					if($cust_options['options'])
				{
					foreach ($cust_options['options'] as $cust_opt)
							{
							$counter=0;
							$customdate =$cust_opt['value'];
							echo "<br>Item Name-".$item_name = $item->getName();
							}		
				}
				$cdate='11 February 2013';
				if($customdate== $cdate)
				{
					if(!$item->getParentItemId() && $item->getProductType()=='bundle')
					{}else{
					//if($item->getProductType()!='bundle')
					echo "<br>".$customdate;
					$ordereditem=$item_name = $item->getName();
					echo "<br>Item ordered Name-".$item_name = $item->getName();
					$qty=$item->getqtyInvoiced();
					echo "Invoice qty=". $item->getqtyInvoiced();
					$counter+=1;
					if($item->getParentItemId())
					{
					if(($item_name=="onedaycleanse"))
					{
						$cone=$cone+1;
						echo "no of onedaycleanse ". $cone;
						$onedayhami[]=$item->getqtyInvoiced();
					}
					if(($item_name=="threedaycleanse"))
					{
						$cthree=$cthree+1;
						echo "noo of threedaycleanse ". $cthree;
						$threedayhami[]=$item->getqtyInvoiced();
					}
					if(($item_name=="fivedaycleanse"))					
					{
						$cfive=$cfive+1;
						echo "noo of fivedaycleanse ". $cfive;
						$fivedayhami[]=$item->getqtyInvoiced();
					}
					}
					}
				}
			}
		}
		
		if($_shippingAddress->getRegion()=='Wellington'){
				
		}
		if($_shippingAddress->getRegion()=='Tauranga'){
	
		}
		
		if($_shippingAddress->getRegion()==''){
		echo "<br>Region field is blank";
		}
	}
}
foreach($oneday as $onedayqty)
{
	$sumone+=$onedayqty;
	echo "sumone".$sumone;
}
print_r($threeday);
foreach($threeday as $threedayqty)
{ 
	$sumthree+=$threedayqty;
	echo "sumthree".$sumthree;
}
foreach($fiveday as $fivedayqty)
{ 
	$sumfive+=$fivedayqty;
	echo "sumfive".$sumfive;
}

foreach($adoneday as $onedayqty)
{
	$sumadone+=$onedayqty;
	echo "sumadone".$sumadone;
}
foreach($adthreeday as $threedayqty)
{ 
	$sumadthree+=$threedayqty;
	echo "sumadthree".$sumadthree;
}
foreach($adfiveday as $fivedayqty)
{ 
	$sumadfive+=$fivedayqty;
	echo "sumadfive".$sumadfive;
}

foreach($onedayhami as $onedayqty)
{ 
	$sumonehami+=$onedayqty;
	echo "sumonehami".$sumonehami;
}
foreach($threedayhami as $threedayqty)
{ 
	$sumthreehami+=$threedayqty;
	echo "sumthreehami".$sumthreehami;
}
foreach($fivedayhami as $fivedayqty)
{ 
	$sumfivehami+=$fivedayqty;
	echo "sumfivehami".$sumfivehami;
}

echo "green".$greenauck=(3*$sumone)+(9*$sumthree)+(15*$sumfive)+(4*$sumadone)+(12*$sumadthree)+(20*$sumadfive);
echo "Yellow".$Yellowauck=(1*$sumone)+(3*$sumthree)+(5*$sumfive)+(0*$sumadone)+(0*$sumadthree)+(0*$sumadfive);
echo "Zesty".$Zestyauck=(1*$sumone)+(3*$sumthree)+(5*$sumfive)+(1*$sumadone)+(3*$sumadthree)+(5*$sumadfive);
echo "cashew".$cashewauck=(1*$sumone)+(3*$sumthree)+(5*$sumfive)+(1*$sumadone)+(3*$sumadthree)+(5*$sumadfive);

echo "green".$greenhami=(3*$sumonehami)+(9*$sumthreehami)+(15*$sumfivehami);
echo "Yellow".$Yellowhami=(1*$sumonehami)+(3*$sumthreehami)+(5*$sumfivehami);
echo "Zesty".$Zestyhami=(1*$sumonehami)+(3*$sumthreehami)+(5*$sumfivehami);
echo "cashew".$cashewhami=(1*$sumonehami)+(3*$sumthreehami)+(5*$sumfivehami);

	

 $items=array(array('Auckland', $greenauck,$Yellowauck,$Zestyauck,$cashewauck), 
			array('Hamilton' ,$greenhami,$Yellowhami,$Zestyhami,$cashewhami),
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
$pdf->save('orderpdf/Productionrrrr Volumes.pdf'); 
?>