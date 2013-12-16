<?php
require_once('app/Mage.php');
include('lib/Zend/Pdf.php');
Mage::app();
$today = getdate();
$d = $today['mday'];
$m = $today['mon'];
$y = $today['year'];
$from = $y.'-'.$m.'-'.$d.' 00:00:00';
//echo $from;
$orders = Mage::getModel('sales/order')
->getCollection()
->addAttributeToSelect("*");
//->addAttributeToFilter('status', 'pending');
/*->addAttributeToFilter('created_at',array(
                    'from'  => '04-02-2013 00:00:00'                 
                 ));*/



if(count($orders)<1){
echo "<p style='color:red'>Order's not found for this store</p>";
die;
}
$orderAddress = array();
$i=0;

foreach ($orders as $order)
{	

if($order->getData('increment_id')){
$shipping_address = $order->getShippingAddress();
if($shipping_address){
$shipping_address=$shipping_address->getData();
$shipping_address = $order->getShippingAddress()->getData();

if($shipping_address['region']){
$orderAddress[$i] = $shipping_address['region'];
}
$i++;
}
}
}
$arr_unique = array_unique($orderAddress);
asort($arr_unique);
$arr_counting = array_count_values($orderAddress);


$pdf = new Zend_Pdf();
$pdf->pages[] = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
$page=$pdf->pages[0]; // this will get reference to the first page.
//$pdf->pages[]=$pdf->newPage(Zend_Pdf_Page::SIZE_A4);

$style = new Zend_Pdf_Style();
$style->setLineColor(new Zend_Pdf_Color_Rgb(0,0,0));

$width=$page->getWidth();
$height=$page->getHeight();
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 18);
$page->drawText('Order for Shipment on '.date("j M Y"),160,($page->getHeight()-90));
$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
$style->setFont($font,12);
$page->setStyle($style);
$page->drawText('Total Numbers of Orders by Region',($width-520),($page->getHeight()-120));
/*$items=array(array('Auckland',8),
		array('Hamilton',3 ),
		array('Tauranga',3),
		array('Wellington',4));*/
$items_count=count($items);

$k=1;
$linewidth=1;

$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 12);
$page->drawLine(70,($height-130),($width-70),($height-130));
$page->drawText('Region',($width-520),($height-142));
$page->drawText('# Orders',($width-120),($height-142));
//$font1 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
$style->setFont($font,12);
$page->setStyle($style);




$itr=0;
foreach($arr_unique as $shipp_loc) {

$page->drawText($shipp_loc,($width-520),($height-160*$k));
$page->drawText($arr_counting[$shipp_loc],($width-80),($height-160*$k));
$page->drawLine(70,(($height-160*$k)+12),($width-70),(($height-160*$k)+12));
 $k+=0.1;
 $page->drawLine(($width-140), ($page->getHeight()-130), ($width-140), (($height-160*$k)+12));
$page->drawLine(($width-525), ($page->getHeight()-130), ($width-525), (($height-160*$k)+12));
 $page->drawLine(70,(($height-160*$k)+12),($width-70),(($height-160*$k)+12));
 $page->drawLine(($width-70), ($page->getHeight()-130), ($width-70), (($height-160*$k)+12));
$itr++;
}

//$page->drawText('Note: Each order follows on a single page which is also used as the packing slip',($width-520),($height-160*$k));


$radius=2;
$i=1;
$j=1;
$m=1;
$regions = array('Auckland','Hamilton','Tauranga','Wellington');
foreach($regions as $region) {
foreach ($orders as $order) {
	$shipping_address = $order->getShippingAddress();
if($shipping_address){
if(($order->getShippingAddress()->getRegion()) == $region) {
	
$pdf->pages[] = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
$page=$pdf->pages[$m]; // this will get reference to the first page.
$page->setStyle($style);

$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 12);
   
$page->drawText('Order #: '.$order->getIncrementId(),250,($page->getHeight()-80));


$shipping_address=$shipping_address->getData();//this will gets shipping address

$page->drawText('Ship to:',80,($page->getHeight()-120));
$page->drawText('Phone: '.$shipping_address['telephone'],340,($page->getHeight()-120));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 12);
$page->drawText($shipping_address['firstname'].' '.$shipping_address['lastname'],80,($page->getHeight()-140));
$page->drawText($shipping_address['street'],80,($page->getHeight()-160));
$page->drawText($shipping_address['region'].', '.$shipping_address['postcode'],80,($page->getHeight()-180));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 12);
$page->drawLine(80,($page->getHeight()-205),540,($page->getHeight()-205));
$page->drawLine(80,($height-205),80,($height-325));
$page->drawLine(130,($height-205),130,($height-325));
$page->drawLine(390,($height-205),390,($height-325));
$page->drawLine(490,($height-205),490,($height-325));
$page->drawLine(540,($height-205),540,($height-325));
$page->drawText('Qty',100,($page->getHeight()-220));
$page->drawText('Product',140,($page->getHeight()-220));
$page->drawText('Delivery Date',400,($page->getHeight()-220));
$page->drawText('Picked',500,($page->getHeight()-220));
//$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 12);
//$page->drawLine(80,($page->getHeight()-225),540,($page->getHeight()-225));
//$page->drawText($order->getTotalQtyOrdered(),110,($page->getHeight()-240));


$items= $order->getAllItems();  //gets all products ordered per order
$r = 0;
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 12);
foreach($items as $item) {

$page->drawLine(80,($page->getHeight()-225),540,($page->getHeight()-225));
$page->drawText('1',110,($page->getHeight()-240));
//echo $item->getParentItemId();
if(!$item->getParentItemId()){
$page->drawText($item->getName() ,140,($page->getHeight()-(240+(80*$r))));

$cust_options= $item->getProductOptions();
			$opt=$cust_options['bundle_options'];
			
			foreach($opt as $op);
			foreach($op['value'] as $bndl){
			$prod_name= $bndl['title'];
			//$prod_qty= $bndl['qty']; 
			} 
if($prod_name=="onedaycleanse" ){
$green="3 x Pure Green";
$yellow="1 x Yellow Hit";
$zesty="1 x Zesty Lemonade";
$cashew="1 x Cashew Dream";
}
if($prod_name=="threedaycleanse" ){
$green="9 x Pure Green";
$yellow="3 x Yellow Hit";
$zesty="3 x Zesty Lemonade";
$cashew="3 x Cashew Dream";
}
if($prod_name=="fivedaycleanse" ){
$green="15 x Pure Green";
$yellow="5 x Yellow Hit";
$zesty="5 x Zesty Lemonade";
$cashew="5 x Cashew Dream";
}
$productitem=array("$green","$yellow","$zesty","$cashew");
foreach($productitem as $pitem)
{
$circ=$page->drawCircle(160,(($height-250*$i)),$radius);
$page->drawText($pitem,170,(($height-255*$i)));
$i+=0.05;
}
}
$page->drawText('30 Jan 2013 ',400,($page->getHeight()-240));
$page->drawLine(80,($page->getHeight()-305),540,($page->getHeight()-305));
$r++;

}

/*$page->drawText('1',110,($page->getHeight()-320));
$page->drawText('Cooler Bag ',140,($page->getHeight()-320));
$page->drawText('30 Jan 2013 ',400,($page->getHeight()-320));
$page->drawLine(80,($page->getHeight()-325),540,($page->getHeight()-325));*/
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 12);
$page->drawText('Special Instructions:',100,($page->getHeight()-420));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 12);
$page->drawText('<dev note: print special instructions from order (comments) here, if any>',100,($page->getHeight()-440));

$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 12);
$page->drawText('Food Allergies: ',100,($page->getHeight()-460));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 12);
$page->drawText('Yes|No (dev note: as per the customer selection on check out) ',100,($page->getHeight()-480));
$m++;
}
}
}
}

 header('Content-type: application/pdf');

      $pdfData = $pdf->render();
      echo $pdfData;
//$pdf->save('orderpdf/test1.pdf');
?>