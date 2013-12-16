<?php
require_once('app/Mage.php');
Mage::app();
include('lib/Zend/Pdf.php');
$pdf = new Zend_Pdf();
$pdf->pages[] = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
$page=$pdf->pages[0]; // this will get reference to the first page.
//$pdf->pages[]=$pdf->newPage(Zend_Pdf_Page::SIZE_A4);

$style = new Zend_Pdf_Style();
$style->setLineColor(new Zend_Pdf_Color_Rgb(0,0,0));

$width=$page->getWidth();
$height=$page->getHeight();
if(isset($_GET['del_date']) && $_GET['del_date']!='' ){
	if(isValid($_GET['del_date'])=='yes'){
	$cur_date=$_GET['del_date'];
	}
	else{
	$cur_date = Mage::getModel('core/date')->date('d F Y');
	
	}
	
}else{
 $cur_date = Mage::getModel('core/date')->date('d F Y');
 }
 $imagePath='logo.jpg'; 
$image = Zend_Pdf_Image::imageWithPath($imagePath); 
$page->drawImage($image, 60, ($page->getHeight()-65), 220, ($page->getHeight()-20));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 9);
$page->drawText('PHD Cleanse Ltd',457,($page->getHeight()-40));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 9);
$page->drawText('Phone: 0800 743 463 (0800 PHD 4 ME)',370,($page->getHeight()-50));
$page->drawText('PO Box 31 866, Milford, 0741',411,($page->getHeight()-60));
$page->drawText('Auckland, NEW ZEALAND',422,($page->getHeight()-70));



$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD ), 15);

$page->drawText('Order for Shipment on '.$cur_date,160,($page->getHeight()-120));
$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
$style->setFont($font,11);
$page->setStyle($style);
$page->drawText('Total Numbers of Orders by Region',($width-520),($page->getHeight()-150));
$orders = Mage::getModel('sales/order')
        ->getCollection()
        ->addAttributeToSelect("*")
		->addAttributeToFilter('status', array('processing','complete'));
		//->addAttributeToFilter('status', 'processing'); //'processing',
		
if (count($orders) < 1) {
   echo "<p style='color:red'>Order's not found for this store</p>";
}
$auck_count=0;$ham_count=0;$taura_count=0;$willi_count=0;
foreach ($orders as $order) {
$flag_auck='false';$flag_ham='false';$flag_will='false'; $flag_taur='false';
  $_shippingAddress = $order->getShippingAddress();
   if ($_shippingAddress) {

      $items = $order->getAllItems();
      if ($_shippingAddress->getRegion() == 'Auckland') {
	 //  echo "<br> Auck". $order->getIncrementId()	."--". $cust_opt['value']; 
	  $lead_day=getLeadday('Auckland');
		$add_day=$lead_day-1;
		$calc_delivery_date = date("d F Y", strtotime($cur_date . " + $add_day day"));
		foreach($items as $item){
		$cust_options= $item->getProductOptions();
			 if ($cust_options['options']) {
               foreach ($cust_options['options'] as $cust_opt) {                 
					$deldate = $cust_opt['value']; 
					if ($calc_delivery_date == date("d F Y", strtotime($deldate))) {
					 $order_id['auck'][]= $order->getIncrementId();
					 $flag_auck='true';
					}
                  
               }
            }
		}
		if($flag_auck=='true'){$auck_count=$auck_count+1;}
	  }
	 if ($_shippingAddress->getRegion() == 'Hamilton') {
	//  echo "<br> Ham". $order->getIncrementId()	."--". $cust_opt['value']; 
		$lead_day=getLeadday('Hamilton');
		$add_day=$lead_day-1;
		$calc_delivery_date = date("d F Y", strtotime($cur_date . " + $add_day day"));
		foreach($items as $item){
		$cust_options= $item->getProductOptions();
			 if ($cust_options['options']) {
               foreach ($cust_options['options'] as $cust_opt) {                 
					$deldate = $cust_opt['value']; 
					if ($calc_delivery_date == date("d F Y", strtotime($deldate))) {
					 $order_id['ham'][]= $order->getIncrementId();
					 $flag_ham='true';
					}
                  
               }
            }
		}
		if($flag_ham=='true'){$ham_count=$ham_count+1;}
	 
	 
		//$ham_count++;	  
	  } 
	  if ($_shippingAddress->getRegion() == 'Wellington') {
	 
	  $lead_day=getLeadday('Wellington');
		$add_day=$lead_day-1;
		$calc_delivery_date = date("d F Y", strtotime($cur_date . " + $add_day day"));
		foreach($items as $item){
		$cust_options= $item->getProductOptions();
			 if ($cust_options['options']) {
               foreach ($cust_options['options'] as $cust_opt) {                 
					 $deldate = $cust_opt['value']; 
					  // echo "<br> wel". $order->getIncrementId()	."--". $cust_opt['value'];
					if ($calc_delivery_date == date("d F Y", strtotime($deldate))) {
					 $order_id['well'][]= $order->getIncrementId();
					  $flag_will='true';
					}
                  
               }
            }
		}
		if($flag_will=='true'){$willi_count=$willi_count+1;} 
		 
	  }
	  if ($_shippingAddress->getRegion() == 'Tauranga') {
	  $lead_day=getLeadday('Tauranga');
		$add_day=$lead_day-1;
		$calc_delivery_date = date("d F Y",strtotime($cur_date . " + $add_day day"));
		foreach($items as $item){
		$cust_options= $item->getProductOptions();
			 if ($cust_options['options']) {
               foreach ($cust_options['options'] as $cust_opt) {                 
					$deldate = $cust_opt['value']; 
					if ($calc_delivery_date == date("d F Y", strtotime($deldate))) {
					 $order_id['taur'][]= $order->getIncrementId();
					 $flag_taur='true';
					}
                  
               }
            }
		}
		if($flag_taur=='true'){$taura_count=$taura_count+1;} 
		//$taura_count++;	  
	  }
	  }
}


$items=array(array("Auckland","$auck_count"),
		array("Hamilton","$ham_count" ),
		array("Tauranga","$taura_count"),
		array("Wellington","$willi_count"));
$items_count=count($items);

$k=1;
$linewidth=1;

$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD ), 12);
$page->drawLine(70,($height-160),($width-70),($height-160));
$page->drawText('Region',($width-520),($height-172));
$page->drawText('# Orders',($width-120),($height-172));
//$font1 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD );
$style->setFont($font,12);
$page->setStyle($style);

$page->drawLine(($width-70), ($page->getHeight()-160), ($width-70), ($page->getHeight()-254));
$page->drawLine(($width-140), ($page->getHeight()-160), ($width-140), ($page->getHeight()-254));
$page->drawLine(($width-525), ($page->getHeight()-160), ($width-525), ($page->getHeight()-254));
for($i=0;$i<=$items_count;$i++)
{

$page->drawText($items[$i][0],($width-520),($height-190*$k));
$page->drawText($items[$i][1],($width-90),($height-190*$k));
   //$linewidth+=
 $page->drawLine(70,(($height-190*$k)+12),($width-70),(($height-190*$k)+12));
 $k+=0.1;
}

/* ******For Aucklan seprate orders */
$m=1;
$auck_unique_ord = array_unique($order_id['auck']);
asort($auck_unique_ord);
foreach( $auck_unique_ord as $auck_orders){
$auc_id=$auck_orders;
$orders_detail_auck = Mage::getModel('sales/order')
        ->getCollection()
        ->addAttributeToSelect("*")
		->addAttributeToFilter('status', array('processing','complete'))
		->addAttributeToFilter('increment_id', $auc_id);
foreach($orders_detail_auck as $auckland_ord){

$entity_id_auck=$auckland_ord->getEntityId();
$items=$auckland_ord->getAllitems();
  $_shippingAddress = $auckland_ord->getShippingAddress();
 //echo "<pre>"; print_r($_shippingAddress);
   if ($_shippingAddress) {

  $ord_id=$auck_orders;
    $first_name=$_shippingAddress->getFirstname(); 
   $last_name=$_shippingAddress->getLastname(); 
   $shipping_address=$_shippingAddress->getData();
   $street=$_shippingAddress->getStreet();
   $region=$_shippingAddress->getRegion();
   $post=$_shippingAddress->getPostcode();
  $phon=$_shippingAddress->getTelephone();
  $city=$_shippingAddress->getCity();
   }

getdetailorders($cur_date,$pdf,$style,$height,$width,$ord_id,$auckland_ord,$first_name ,$last_name,$street,$region,$post,$phon ,$items,$m,$entity_id_auck,$city);

}
$m++;
}

/* For Hamilton */
//$h=$m+1;
$ham_unique_ord = array_unique($order_id['ham']);
asort($ham_unique_ord);
foreach( $ham_unique_ord as $ham_orders){
$ham_id=$ham_orders;
$orders_detail_ham = Mage::getModel('sales/order')
        ->getCollection()
        ->addAttributeToSelect("*")
		->addAttributeToFilter('status', array('processing','complete'))
		->addAttributeToFilter('increment_id', $ham_id);
foreach($orders_detail_ham as $ham_ord){
$entity_id_ham=$ham_ord->getEntityId();
$items=$ham_ord->getAllitems();
  $_shippingAddress = $ham_ord->getShippingAddress();
 //echo "<pre>"; print_r($_shippingAddress);
   if ($_shippingAddress) {

  $ord_id=$ham_orders;
    $first_name=$_shippingAddress->getFirstname(); 
   $last_name=$_shippingAddress->getLastname(); 
   $shipping_address=$_shippingAddress->getData();
  $street=$_shippingAddress->getStreet();
   $region=$_shippingAddress->getRegion();
   $post=$_shippingAddress->getPostcode();
  $phon=$_shippingAddress->getTelephone();
  $city=$_shippingAddress->getCity();
   }
getdetailorders($cur_date,$pdf,$style,$height,$width,$ord_id,$ham_ord,$first_name ,$last_name,$street,$region,$post,$phon ,$items,$m,$entity_id_ham,$city);

}
$m++;
}
/* ******Welll******* */
//$w=$h+1;
$well_unique_ord = array_unique($order_id['well']);
asort($well_unique_ord);
foreach( $well_unique_ord as $well_orders){
$well_id=$well_orders;
$orders_detail_well = Mage::getModel('sales/order')
        ->getCollection()
        ->addAttributeToSelect("*")
		->addAttributeToFilter('status', array('processing','complete'))
		->addAttributeToFilter('increment_id', $well_id);
foreach($orders_detail_well as $well_ord){
$entity_id_well=$well_ord->getEntityId();
$items=$well_ord->getAllitems();
  $_shippingAddress = $well_ord->getShippingAddress();
 //echo "<pre>"; print_r($_shippingAddress);
   if ($_shippingAddress) {

  $ord_id=$well_orders;
    $first_name=$_shippingAddress->getFirstname(); 
   $last_name=$_shippingAddress->getLastname(); 
   $shipping_address=$_shippingAddress->getData();
   $street=$_shippingAddress->getStreet();
   $region=$_shippingAddress->getRegion();
   $post=$_shippingAddress->getPostcode();
  $phon=$_shippingAddress->getTelephone();
  $city=$_shippingAddress->getCity();
   /* echo "<pre>";
  print_r($_shippingAddress); */
   }
getdetailorders($cur_date,$pdf,$style,$height,$width,$ord_id,$well_ord,$first_name ,$last_name,$street,$region,$post,$phon ,$items,$m,$entity_id_well,$city);

}
$m++;
}
/* ******** */

/* ******Taur******* */
//$t=$m+1;
$taur_unique_ord = array_unique($order_id['taur']);
asort($taur_unique_ord);
foreach( $taur_unique_ord as $taur_orders){
$taur_id=$taur_orders;
$orders_detail_taur = Mage::getModel('sales/order')
        ->getCollection()
        ->addAttributeToSelect("*")
		->addAttributeToFilter('status', array('processing','complete'))
		->addAttributeToFilter('increment_id', $taur_id);
foreach($orders_detail_taur as $taur_ord){
$entity_id=$taur_ord->getEntityId();
$items=$taur_ord->getAllitems();
  $_shippingAddress = $taur_ord->getShippingAddress();
 //echo "<pre>"; print_r($_shippingAddress);
   if ($_shippingAddress) {

  $ord_id=$taur_orders;
    $first_name=$_shippingAddress->getFirstname(); 
   $last_name=$_shippingAddress->getLastname(); 
   $shipping_address=$_shippingAddress->getData();
 $street=$_shippingAddress->getStreet();
   $city=$_shippingAddress->getCity();
   $region=$_shippingAddress->getRegion();
   $post=$_shippingAddress->getPostcode();
  $phon=$_shippingAddress->getTelephone();
 
   }

getdetailorders($cur_date,$pdf,$style,$height,$width,$ord_id,$taur_ord,$first_name ,$last_name,$street,$region,$post,$phon ,$items,$m,$entity_id,$city);

}
$m++;
}
/* ******** */




function getdetailorders($cur_date,$pdf,$style,$height,$width,$ord_id,$ord_obj,$first_name ,$last_name,$street,$region,$post,$phon ,$items,$m,$entity_id,$city){

if($ord_obj){
$lead_day=getLeadday($region);
		$add_day=$lead_day-1;
		$calc_delivery_date = date("d F Y", strtotime($cur_date . " + $add_day day"));	
$pdf->pages[] = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
$page=$pdf->pages[$m]; // this will get reference to the first page.
$page->setStyle($style);

//$productitem=array('3 x Pure Green','1 x Yellow Hit','1 x Zesty Lemonade','1 x Cashew Dream');
$radius=2;
$i=1;
 $imagePath='logo.jpg'; 
$image = Zend_Pdf_Image::imageWithPath($imagePath); 

$page->drawImage($image, 60, ($page->getHeight()-65), 220, ($page->getHeight()-20));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 9);
$page->drawText('PHD Cleanse Ltd',457,($page->getHeight()-40));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA),9);
$page->drawText('Phone: 0800 743 463 (0800 PHD 4 ME)',370,($page->getHeight()-50));
$page->drawText('PO Box 31 866, Milford, 0741',411,($page->getHeight()-60));
$page->drawText('Auckland, NEW ZEALAND',422,($page->getHeight()-70));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD ), 10);
$page->drawText('Order #:'.$ord_id,250,($page->getHeight()-110));
$page->drawText('Ship to:',80,($page->getHeight()-140));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 9);
$page->drawText($first_name." ".$last_name,80,($page->getHeight()-155));
$page->drawText('Phone: '.$phon,457,($page->getHeight()-130));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 8);
$add1=$street[0];
$page->drawText(trim($add1),80,($page->getHeight()-165));
if($street[1]){
$add2=$street[1];
$page->drawText(trim($add2),80,($page->getHeight()-175));
$page->drawText(trim($city).",".$post,80,($page->getHeight()-185));
$page->drawText(trim($region),80,($page->getHeight()-195));
}else{
$page->drawText(trim($city).",".$post,80,($page->getHeight()-175));
$page->drawText(trim($region),80,($page->getHeight()-185));
}

$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD ), 12);
$page->drawLine(80,($page->getHeight()-205),540,($page->getHeight()-205));


$page->drawText('Qty',100,($page->getHeight()-220));
$page->drawText('Product',140,($page->getHeight()-220));
$page->drawText('Delivery Date',400,($page->getHeight()-220));
$page->drawText('Picked',500,($page->getHeight()-220));
$page->drawLine(80,($page->getHeight()-225),540,($page->getHeight()-225));
$items= $ord_obj->getAllItems();

foreach($items as $item) {
$h=0;
$j=1.06;
/* if($item->getName()=="Maintenance"){$Z=170;} */

if($item->getProductType() == 'bundle'){
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 9);
$cust_options= $item->getProductOptions();
if ($cust_options['options']) {
               foreach ($cust_options['options'] as $cust_opt) {
                  $counter = 0;
             $deldate = $cust_opt['value']; 
                  //echo "<br>Item Name-".$item_name = $item->getName();
               }
            }
			$opt=$cust_options['bundle_options'];
			foreach($opt as $op);
			foreach($op['value'] as $bndl){
			$prod_name= $bndl['title'];
			//$prod_qty= $bndl['qty']; 
			} 
if ($calc_delivery_date == date("d F Y", strtotime($deldate))) {
$page->drawText(round($item->getQtyInvoiced(),0),110,($page->getHeight()-245*($i-0.03)));
$page->drawText($item->getName() ,140,($page->getHeight()-245*($i-0.03)));
if($prod_name!="maintenance_pro")
$page->drawText($prod_name,150,(($page->getHeight()-255*($i-0.02))));
if($prod_name=="onedaycleanse" ){
$green="3 x Pure Green";
$yellow="1 x Yellow Hit";
$zesty="1 x Zesty Lemonade";
$cashew="1 x Cashew Dream";
$tbl_h=314;
}
if($prod_name=="threedaycleanse" ){
$green="9 x Pure Green";
$yellow="3 x Yellow Hit";
$zesty="3 x Zesty Lemonade";
$cashew="3 x Cashew Dream";
$tbl_h=314;
}
if($prod_name=="fivedaycleanse" ){
$green="15 x Pure Green";
$yellow="5 x Yellow Hit";
$zesty="5 x Zesty Lemonade";
$cashew="5 x Cashew Dream";
$tbl_h=314;
}
if($prod_name=="advanceonedaycleanse" ){
$green="4 x Pure Green";
$zesty="1 x Zesty Lemonade";
$cashew="1 x Cashew Dream";
$yellow="";
$tbl_h=304;
}if($prod_name=="advancethreedaycleanse" ){
$green="12 x Pure Green";
$zesty="3 x Zesty Lemonade";
$cashew="3 x Cashew Dream";
$yellow="";
$tbl_h=304;
}if($prod_name=="advancefivedaycleanse" ){
$green="20 x Pure Green";
$zesty="5 x Zesty Lemonade";
$cashew="5 x Cashew Dream";
$yellow="";
$tbl_h=304;
}if($prod_name=="maintenance_pro" ){
$green="6 x Pure Green";
$zesty="";
$cashew="";
$yellow="";
$tbl_h=290;
}
$page->drawLine(80,($height-205-$h),80,($height-$tbl_h-$h));
$page->drawLine(130,($height-205-$h),130,($height-$tbl_h-$h));
$page->drawLine(390,($height-205-$h),390,($height-$tbl_h-$h));
$page->drawLine(490,($height-205-$h),490,($height-$tbl_h-$h));
$page->drawLine(540,($height-205-$h),540,($height-$tbl_h-$h));   
$productitem=array("$green","$yellow","$zesty","$cashew");


foreach($productitem as $pitem)
{
 if (empty($pitem)) {
    continue;
  }else{

$circ=$page->drawCircle(160,(($page->getHeight()-250*($j-0.02))),$radius);
$page->drawText($pitem,170,(($page->getHeight()-255*($j-0.02))));
$j+=0.06;$i+=0.06;
}
}

$page->drawText(date("d F Y", strtotime($deldate)),400,($page->getHeight()-240));
$page->drawLine(80,($page->getHeight()-258*($i)),540,($page->getHeight()-258*($i)));
}
$h=$h+$j+2;
$i+=0.06;
}
else if($item->getProductType() == 'simple' && !$item->getParentItemId()){
$h=$h+1.09;
$page->drawLine(80,($height-310-$h),80,($height-338-$h));
$page->drawLine(130,($height-310-$h),130,($height-338-$h));
$page->drawLine(390,($height-310-$h),390,($height-338-$h));
$page->drawLine(490,($height-310-$h),490,($height-338-$h));
$page->drawLine(540,($height-310-$h),540,($height-338-$h));
$page->drawText(round($item->getQtyInvoiced(),0),110,($page->getHeight()-320*($j-0.03)));
$page->drawText($item->getName() ,140,($page->getHeight()-320*($j-0.03)));
$page->drawText(date("d F Y", strtotime($deldate)),400,($page->getHeight()-320*($j-0.03)));
$page->drawLine(80,($page->getHeight()-320*($j)),540,($page->getHeight()-320*($j)));
$h=$h+$j;
}
}
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD ), 12);
$attr_id=getExtrafeild($entity_id);

	$y = $page->getHeight()-353*$i;
	$lines = explode("\n",getWrappedText($attr_id[0],$style,500)) ;

if($attr_id[0]!=""){
	$page->drawText('Special Instructions:',100,($page->getHeight()-340*$i));
	$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 9);
	foreach($lines as $line)
	{
		$page->drawText($line, 100, $y);
		$y-=15;
		
	}
}
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD ), 12);
if($attr_id[1]=='0'){
$page->drawText('Food Allergies: ',100,$y);
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 9);
$page->drawText('No',100,$y-15); 
}
else{
$page->drawText('Food Allergies: ',100,($page->getHeight()-380*$i));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 9);
$page->drawText('Yes',100,($page->getHeight()-390*$i)); 
}

}
}
header('Content-type: application/pdf');
$pdfData = $pdf->render();
echo $pdfData;
$pdf->save('orderpdf/Orders_Shipment.pdf');
//if(isset($_GET['email']) && $_GET['email']==1){

	 if(smail()){
	emptyDir('orderpdf');
	 }
//}
?>
<?php 

function getLeadday($region_name){
   $resource_lead = Mage::getSingleton('core/resource');
   $readConnection_lead = $resource_lead->getConnection('core_read');
$region_id=getOrderRegionId($region_name);
$lead_day_query = 'SELECT   lead_days FROM diji_salesregions_delivery_days where region_id=' . $region_id;
			$results_lead_day = $readConnection_lead->fetchAll($lead_day_query);
			if($results_lead_day){ 
				foreach ($results_lead_day as $lead_days) {
						$lead_day=$lead_days['lead_days'];
				}   
			}
			else{
			   $lead_day="0";			   
			   }
	return  $lead_day;
}
function getOrderRegionId($name){
   $resource = Mage::getSingleton('core/resource');
   $readConnection = $resource->getConnection('core_read');
   $region = 'SELECT  region_id FROM diji_salesregions_region where name="'.$name.'"';
   $results = $readConnection->fetchAll($region);
	if($results){
		foreach($results as $res){
		$res_id= $res['region_id'];
		}
	}
return $res_id;
}
 function getExtrafeild($order_id){
   $resource_ex = Mage::getSingleton('core/resource');
   $readConnection_ex = $resource_ex->getConnection('core_read');
   $fields = 'SELECT  * FROM belvg_checkoutfields_orders where order_id="'.$order_id.'"';
   $results = $readConnection_ex->fetchAll($fields);
	if($results){
		foreach($results as $res){
		if($res['attribute_id']=='146')
		$extra[]=$res['value'];
		if($res['attribute_id']=='148')
		$extra[]=$res['value'];
		
		}
	}
return $extra;

} 
function isValid($date){
$date_format = 'd F Y';
$input = $date;
$input = trim($input);
$time = strtotime($input);
$is_valid = date($date_format, $time) == $input;
return $is_valid ? 'yes' : 'no';
}
 function getWrappedText($string, Zend_Pdf_Style $style,$max_width)
{
    $wrappedText = '' ;
    $lines = explode("\n",$string) ;
    foreach($lines as $line) {
         $words = explode(' ',$line) ;
         $word_count = count($words) ;
         $i = 0 ;
         $wrappedLine = '' ;
         while($i < $word_count)
         {
             /* if adding a new word isn't wider than $max_width,
             we add the word */
             if(widthForStringUsingFontSize($wrappedLine.' '.$words[$i]
                 ,$style->getFont()
                 , $style->getFontSize()) < $max_width) {
                 if(!empty($wrappedLine)) {
                     $wrappedLine .= ' ' ;
                 }
                 $wrappedLine .= $words[$i] ;
             } else {
                 $wrappedText .= $wrappedLine."\n" ;
                 $wrappedLine = $words[$i] ;
             }
             $i++ ;
         }
         $wrappedText .= $wrappedLine."\n" ;
     }
     return $wrappedText ;
}

/**
 * found here, not sure of the author :
 * http://devzone.zend.com/article/2525-Zend_Pdf-tutorial#comments-2535
 */
  function widthForStringUsingFontSize($string, $font, $fontSize)
 {
     $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
     $characters = array();
     for ($i = 0; $i < strlen($drawingString); $i++) {
         $characters[] = (ord($drawingString[$i++]) << 8 ) | ord($drawingString[$i]);
     }
     $glyphs = $font->glyphNumbersForCharacters($characters);
     $widths = $font->widthsForGlyphs($glyphs);
     $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
     return $stringWidth;
 }
 function smail(){
$mail = new Zend_Mail();
$mail->setType(Zend_Mime::MULTIPART_RELATED);
$mail->setBodyHtml("");
$mail->setFrom('sales@phdcleanse.co.nz', 'PHD Sales');
$mail->addTo('orders@phdcleanse.co.nz', 'PHD Order');
//$mail->addTo('anil.gupta@techinflo.com', 'PHD Order');
$mail->setSubject('Orders For Shipment');
$fileContents = file_get_contents('orderpdf/Orders_Shipment.pdf');
$file = $mail->createAttachment($fileContents);
$file->filename = "Orders_for_Shipment.pdf";
$mail->send();
return true;
}
?>
<?php
 
function emptyDir($path) { 
 
     // init the debug string
     $debugStr = '';
     $debugStr .= "Deleting Contents Of: $path<br /><br />";
 
     // parse the folder
     IF ($handle = OPENDIR($path)) {
 
          WHILE (FALSE !== ($file = READDIR($handle))) {
 
               IF ($file != "." && $file != "..") {
 
               // If it's a file, delete it
               IF(IS_FILE($path."/".$file)) {
 
                    IF(UNLINK($path."/".$file)) {
                    $debugStr .= "Deleted File: ".$file."<br />";     
                    }
 
               } ELSE {
 
                    // It's a directory...
                    // crawl through the directory and delete the contents               
                    IF($handle2 = OPENDIR($path."/".$file)) {
 
                         WHILE (FALSE !== ($file2 = READDIR($handle2))) {
 
                              IF ($file2 != "." && $file2 != "..") {
                                   IF(UNLINK($path."/".$file."/".$file2)) {
                                   $debugStr .= "Deleted File: $file/$file2<br />";     
                                   }
                              }
 
                         }
 
                    }
 
                    IF(RMDIR($path."/".$file)) {
                    $debugStr .= "Directory: ".$file."<br />";     
                    }
 
               }
 
               }
 
          }
 
     }
     RETURN $debugStr;
}
?>