<?php

require_once('app/Mage.php');
Mage::app();
include('lib/Zend/Pdf.php');
$pdf = new Zend_Pdf();
$pdf->pages[] = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
$page = $pdf->pages[0]; // this will get reference to the first page.
$style = new Zend_Pdf_Style();
$style->setLineColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
$style->setFont($font, 12);
$page->setStyle($style);
$page->drawText('Production Volumes:'.date("j M Y"),180,($page->getHeight()-100));
$allStores = Mage::app()->getStores();
$current_date = date("j M Y");//Mage::getModel('core/date')->date('d F Y');
$orders = Mage::getModel('sales/order')
        ->getCollection()
        ->addAttributeToSelect("*");
if (count($orders) < 1) {
   echo "<p style='color:red'>Order's not found for this store</p>";
}
$oneday_count = array();
$five_count = array();
$three_count = array();
foreach ($orders as $order) {
echo $cdate=Mage::getModel('core/date')->date('d F Y');die();
   echo $_shippingAddress = $order->getShippingAddress();
   if ($_shippingAddress) {
      $items = $order->getAllItems();
      if ($_shippingAddress->getRegion() == 'Auckland') {
         //echo "<br><b>". $_shippingAddress->getRegion()."</b>";
         //	echo "<br>Order No".$order_id=$order->getIncrementId();
         $items = $order->getAllItems();
         //echo "<pre>";	print_r($items);


         foreach ($items as $itemId => $item) {
            $cust_options = $item->getProductOptions();
            if ($cust_options['options']) {
               foreach ($cust_options['options'] as $cust_opt) {
                  $counter = 0;
                  $deldate = $cust_opt['value'];
                  //echo "<br>Item Name-".$item_name = $item->getName();
               }
            }
            $cdate = '11 February 2013';
            if ($deldate == $cdate) {
               //echo "<pre>";print_r($item);
               if (!$item->getParentItemId() && $item->getProductType() == 'bundle') {
                  //skip
               }
               if ($item->getParentItemId()) {

                  $item_name = $item->getName();
                  if ($item_name == "onedaycleanse") {
                     $oneday_count['auck']['oneday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "threedaycleanse") {
                     $three_count['auck']['threeday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "fivedaycleanse") {
                     $five_count['auck']['fiveday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "advanceonedaycleanse") {
                     $oneday_count['auck']['ad_oneday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "advancethreedaycleanse") {
                     $three_count['auck']['ad_threeday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "advancefivedaycleanse") {
                     $five_count['auck']['ad_fiveday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "maintenance_pro") {
                     $maint_count['auck']['maintenance_pro'][] = $item->getQtyInvoiced();
                  }



                  //here comes onr day two day or 3 day 
               }
            } else {
               //echo "<br> Date Not matched for $item->getName()";
            }
         }
      }

      if ($_shippingAddress->getRegion() == 'Hamilton') {

         //echo "<pre>";	print_r($items);


         foreach ($items as $itemId => $item) {
            $cust_options = $item->getProductOptions();
            if ($cust_options['options']) {
               foreach ($cust_options['options'] as $cust_opt) {
                  $counter = 0;
                  $deldate = $cust_opt['value'];
                  //echo "<br>Item Name-".$item_name = $item->getName();
               }
            }
            $cdate = '11 February 2013';
            if ($deldate == $cdate) {
               //echo "<pre>";print_r($item);
               if (!$item->getParentItemId() && $item->getProductType() == 'bundle') {
                  //skip
               }
               if ($item->getParentItemId()) {

                  $item_name = $item->getName();
                  if ($item_name == "onedaycleanse") {
                     $oneday_count['hamilton']['oneday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "threedaycleanse") {
                     $three_count['hamilton']['threeday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "fivedaycleanse") {
                     $five_count['hamilton']['fiveday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "advanceonedaycleanse") {
                     $oneday_count['hamilton']['ad_oneday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "advancethreedaycleanse") {
                     $three_count['hamilton']['ad_threeday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "advancefivedaycleanse") {
                     $five_count['hamilton']['ad_fiveday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "maintenance_pro") {
                     $maint_count['hamilton']['maintenance_pro'][] = $item->getQtyInvoiced();
                  }



                  //here comes onr day two day or 3 day 
               }
            } else {
               //echo "<br> Date Not matched for $item->getName()";
            }
         }
      }
      if ($_shippingAddress->getRegion() == 'Wellington') {
         foreach ($items as $itemId => $item) {
            $cust_options = $item->getProductOptions();
            if ($cust_options['options']) {
               foreach ($cust_options['options'] as $cust_opt) {
                  $counter = 0;
                  $deldate = $cust_opt['value'];
                  //echo "<br>Item Name-".$item_name = $item->getName();
               }
            }
            $cdate = '11 February 2013';
            if ($deldate == $cdate) {
               //echo "<pre>";print_r($item);
               if (!$item->getParentItemId() && $item->getProductType() == 'bundle') {
                  //skip
               }
               if ($item->getParentItemId()) {

                  $item_name = $item->getName();
                  if ($item_name == "onedaycleanse") {
                     $oneday_count['welli']['oneday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "threedaycleanse") {
                     $three_count['welli']['threeday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "fivedaycleanse") {
                     $five_count['welli']['fiveday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "advanceonedaycleanse") {
                     $oneday_count['welli']['ad_oneday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "advancethreedaycleanse") {
                     $three_count['welli']['ad_threeday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "advancefivedaycleanse") {
                     $five_count['welli']['ad_fiveday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "maintenance_pro") {
                     $maint_count['welli']['maintenance_pro'][] = $item->getQtyInvoiced();
                  }



                  //here comes onr day two day or 3 day 
               }
            } else {
               //echo "<br> Date Not matched for $item->getName()";
            }
         }
      }
      if ($_shippingAddress->getRegion() == 'Tauranga') {

         foreach ($items as $itemId => $item) {
            $cust_options = $item->getProductOptions();
            if ($cust_options['options']) {
               foreach ($cust_options['options'] as $cust_opt) {
                  $counter = 0;
                  $deldate = $cust_opt['value'];
                  //echo "<br>Item Name-".$item_name = $item->getName();
               }
            }
            $cdate = '11 February 2013';
            if ($deldate == $cdate) {
               //echo "<pre>";print_r($item);
               if (!$item->getParentItemId() && $item->getProductType() == 'bundle') {
                  //skip
               }
               if ($item->getParentItemId()) {

                  $item_name = $item->getName();
                  if ($item_name == "onedaycleanse") {
                     $oneday_count['tauranga']['oneday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "threedaycleanse") {
                     $three_count['tauranga']['threeday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "fivedaycleanse") {
                     $five_count['tauranga']['fiveday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "advanceonedaycleanse") {
                     $oneday_count['tauranga']['ad_oneday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "advancethreedaycleanse") {
                     $three_count['tauranga']['ad_threeday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "advancefivedaycleanse") {
                     $five_count['tauranga']['ad_fiveday'][] = $item->getQtyInvoiced();
                  }
                  if ($item_name == "maintenance_pro") {
                     $maint_count['tauranga']['maintenance_pro'][] = $item->getQtyInvoiced();
                  }



                  //here comes onr day two day or 3 day 
               }
            } else {
               //echo "<br> Date Not matched for $item->getName()";
            }
         }
      }
      if ($_shippingAddress->getRegion() == '') {

         //	echo "<br>Region field is blank";
      }
   }
}

//For Auckland

//*****************************
$total_one = 0;
$total_three = 0;
$total_five = 0;
$total_one_ad = 0;
$total_three_ad = 0;
$total_five_ad = 0;
$total_maint = 0;
foreach ($oneday_count['auck']['oneday'] as $one_day) {
   $total_one = $total_one + $one_day;
}
foreach ($three_count['auck']['threeday'] as $three_day) {
   $total_three = $total_three + $three_day;
}
foreach ($five_count['auck']['fiveday'] as $five_day) {
   $total_five = $total_five + $five_day;
}
//**************

foreach ($oneday_count['auck']['ad_oneday'] as $one_day) {
   $total_one_ad = $total_one_ad + $one_day;
}

foreach ($three_count['auck']['ad_threeday'] as $three_day) {
   $total_three_ad = $total_three_ad + $three_day;
}
foreach ($five_count['auck']['ad_fiveday'] as $five_day) {
   $total_five_ad = $total_five_ad + $five_day;
}
foreach ($maint_count['auck']['maintenance_pro'] as $maint) {
   $total_maint = $total_maint + $maint;
}

//****************
//*****************************
$total_one_ham = 0;
$total_three_ham = 0;
$total_five_ham = 0;
$total_one_ad_ham = 0;
$total_three_ad_ham = 0;
$total_five_ad_ham = 0;
$total_maint_ham = 0;
foreach ($oneday_count['hamilton']['oneday'] as $one_day) {
   $total_one_ham = $total_one_ham + $one_day;
}
foreach ($three_count['hamilton']['threeday'] as $three_day) {
   $total_three_ham = $total_three_ham + $three_day;
}
foreach ($five_count['hamilton']['fiveday'] as $five_day) {
   $total_five_ham = $total_five_ham + $five_day;
}
//**************
//Advanced product
foreach ($oneday_count['hamilton']['ad_oneday'] as $one_day) {
   $total_one_ad_ham = $total_one_ad_ham + $one_day;
}
foreach ($three_count['hamilton']['ad_threeday'] as $three_day) {
   $total_three_ad_ham = $total_three_ad_ham + $three_day;
}
foreach ($five_count['hamilton']['ad_fiveday'] as $five_day) {
   $total_five_ad_ham = $total_five_ad_ham + $five_day;
}
foreach ($maint_count['hamilton']['maintenance_pro'] as $maint) {
   $total_maint_ham = $total_maint_ham + $maint;
}

//****************
//welli
//*****************************
$total_one_welli = 0;
$total_three_welli = 0;
$total_five_welli = 0;
$total_one_ad_welli = 0;
$total_three_ad_welli = 0;
$total_five_ad_welli = 0;
$total_maint_welli = 0;
foreach ($oneday_count['welli']['oneday'] as $one_day) {
   $total_one_welli = $total_one_welli + $one_day;
}
foreach ($three_count['welli']['threeday'] as $three_day) {
   $total_three_welli = $total_three_welli + $three_day;
}
foreach ($five_count['welli']['fiveday'] as $five_day) {
   $total_five_welli = $total_five_welli + $five_day;
}
//**************
//Advanced product
foreach ($oneday_count['welli']['ad_oneday'] as $one_day) {
   $total_one_ad_welli = $total_one_ad_welli + $one_day;
}
foreach ($three_count['welli']['ad_threeday'] as $three_day) {
   $total_three_ad_welli = $total_three_ad_welli + $three_day;
}
foreach ($five_count['welli']['ad_fiveday'] as $five_day) {
   $total_five_ad_welli = $total_five_ad_welli + $five_day;
}
foreach ($maint_count['welli']['maintenance_pro'] as $maint) {
   $total_maint_welli = $total_maint_welli + $maint;
}

//****************
//***tauranga*************
//*****************************

foreach ($oneday_count['tauranga']['oneday'] as $one_day) {
   $total_one_tra = $total_one_tra + $one_day;
}
foreach ($three_count['tauranga']['threeday'] as $three_day) {
   $total_three_tra = $total_three_tra + $three_day;
}
foreach ($five_count['tauranga']['fiveday'] as $five_day) {
   $total_five_tra = $total_five_tra + $five_day;
}
//**************
//Advanced product
foreach ($oneday_count['tauranga']['ad_oneday'] as $one_day) {
   $total_one_ad_tra = $total_one_ad_tra + $one_day;
}
foreach ($three_count['tauranga']['ad_threeday'] as $three_day) {
   $total_three_ad_tra = $total_three_ad_tra + $three_day;
}
foreach ($five_count['tauranga']['ad_fiveday'] as $five_day) {
   $total_five_ad_tra = $total_five_ad_tra + $five_day;
}
foreach ($maint_count['tauranga']['maintenance_pro'] as $maint) {
   $total_maint_tra = $total_maint_tra + $maint;
}
//*****AUCK***********
$pure_green = (3 * $total_one + 9 * $total_three + 15 * $total_five) + (4 * $total_one_ad + 12 * $total_three_ad + 20 * $total_five_ad) + (6 * $total_maint);
$yellow = 1 * $total_one + 3 * $total_three + 5 * $total_five;
$zesty_lemonade = (1 * $total_one + 3 * $total_three + 5 * $total_five) + (1 * $total_one_ad + 3 * $total_three_ad + 5 * $total_five_ad);
$cashewdream = (1 * $total_one + 3 * $total_three + 5 * $total_five) + (1 * $total_one_ad + 3 * $total_three_ad + 5 * $total_five_ad);
////////////HAMIL/////////////////////
$pure_green_ham = (3 * $total_one_ham + 9 * $total_three_ham + 15 * $total_five_ham) + (4 * $total_one_ad_ham + 12 * $total_three_ad_ham + 20 * $total_five_ad_ham) + (6 * $total_maint_ham);
$yellow_ham = 1 * $total_one_ham + 3 * $total_three_ham + 5 * $total_five_ham;
$zesty_lemonade_ham = (1 * $total_one_ham + 3 * $total_three_ham + 5 * $total_five_ham) + (1 * $total_one_ad_ham + 3 * $total_three_ad_ham + 5 * $total_five_ad_ham);
$cashewdream_ham = (1 * $total_one_ham + 3 * $total_three_ham + 5 * $total_five_ham) + (1 * $total_one_ad_ham + 3 * $total_three_ad_ham + 5 * $total_five_ad_ham);
///////////////WILL////////////////
$pure_green_welli = (3 * $total_one_welli + 9 * $total_three_welli + 15 * $total_five_welli) + (4 * $total_one_ad_welli + 12 * $total_three_ad_welli + 20 * $total_five_ad_welli) + (6 * $total_maint_welli);
$yellow_welli = 1 * $total_one_welli + 3 * $total_three_welli + 5 * $total_five_welli;
$zesty_lemonade_welli = (1 * $total_one_welli + 3 * $total_three_welli + 5 * $total_five_welli) + (1 * $total_one_ad_welli + 3 * $total_three_ad_welli + 5 * $total_five_ad_welli);
$cashewdream_welli = (1 * $total_one_welli + 3 * $total_three_welli + 5 * $total_five_welli) + (1 * $total_one_ad_welli + 3 * $total_three_ad_welli + 5 * $total_five_ad_welli);
//////////////TAURA////////////////////
$pure_green_tra = (3 * $total_one_tra + 9 * $total_three_tra + 15 * $total_five_tra) + (4 * $total_one_ad_tra + 12 * $total_three_ad_tra + 20 * $total_five_ad_tra) + (6 * $total_maint_tra);
$yellow_tra = 1 * $total_one_tra + 3 * $total_three_tra + 5 * $total_five_tra;
$zesty_lemonade_tra = (1 * $total_one_tra + 3 * $total_three_tra + 5 * $total_five_tra) + (1 * $total_one_ad_tra + 3 * $total_three_ad_tra + 5 * $total_five_ad_tra);
$cashewdream_tra = (1 * $total_one_tra + 3 * $total_three_tra + 5 * $total_five_tra) + (1 * $total_one_ad_tra + 3 * $total_three_ad_tra + 5 * $total_five_ad_tra);
////////////////
$grand_total_green=$pure_green+$pure_green_ham+$pure_green_tra+$pure_green_welli;
$grand_total_yellow=$yellow+$yellow_ham+$yellow_tra+$yellow_welli;
$grand_total_zesty_lemonade=$zesty_lemonade+$zesty_lemonade_ham+$zesty_lemonade_tra+$zesty_lemonade_welli;
$grand_total_cashewdream=$cashewdream+$cashewdream_ham+$cashewdream_tra+$cashewdream_welli;
$items = array(array("Auckland", "$pure_green", "$yellow", "$zesty_lemonade", "$cashewdream"),
    array('Hamilton', "$pure_green_ham", "$yellow_ham", "$zesty_lemonade_ham", "$cashewdream_ham"),
    array('Tauranga',"$pure_green_tra", "$yellow_tra", "$zesty_lemonade_tra", "$cashewdream_tra" ),
    array('Wellington', "$pure_green_welli", "$yellow_welli", "$zesty_lemonade_welli", "$cashewdream_welli"),
	
	);
$height = $page->getHeight();
$width = $page->getWidth();
$counter = 1;
$items_count = count($items);

$page->drawLine(70, ($page->getHeight() - 110), ($width - 70), ($page->getHeight() - 110));
$page->drawLine(($width - 70), ($page->getHeight() - 110), ($width - 70), ($page->getHeight() - 198));
$page->drawLine(($width - 160), ($page->getHeight() - 110), ($width - 160), ($page->getHeight() - 198));
$page->drawLine(($width - 250), ($page->getHeight() - 110), ($width - 250), ($page->getHeight() - 198));
$page->drawLine(($width - 330), ($page->getHeight() - 110), ($width - 330), ($page->getHeight() - 198));
$page->drawLine(($width - 415), ($page->getHeight() - 110), ($width - 415), ($page->getHeight() - 198));
$page->drawLine(($width - 525), ($page->getHeight() - 110), ($width - 525), ($page->getHeight() - 198));

//$page->drawLine(70, ($page->getHeight()-110), 70, ($page->getHeight()-230));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 10);
$page->drawText('Region', 80, $page->getHeight() - 123);
$page->drawtext('Pure Green', 210, ($page->getHeight() - 123));
$page->drawText('Yellow Hit', 290, ($page->getHeight() - 123));
$page->drawText('Zesty Lemonade', 360, ($page->getHeight() - 123));
$page->drawText('Cashew Dream', 450, ($page->getHeight() - 123));
//$page->drawLine(70, ($page->getHeight()-130), 460, ($page->getHeight()-130));
//$page->drawLine(155, ($page->getHeight()-110), 155, ($page->getHeight()-230));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 12);
for ($i = 0; $i <= $items_count; $i++) {
   $page->drawText($items[$i][0], 80, ($height - 140 * $counter));
   $page->drawtext($items[$i][1], 240, ($height - 140 * $counter));
   $page->drawtext($items[$i][2], 330, ($height - 140 * $counter));
   $page->drawtext($items[$i][3], 420, ($height - 140 * $counter));
   $page->drawtext($items[$i][4], 500, ($height - 140 * $counter));
   $page->drawLine(70, (($height - 140 * $counter) + 12), ($width - 70), (($height - 140 * $counter) + 12));

   $counter+=0.1;
}
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 12);
$page->drawText('Total', 80, ($page->getHeight() - 195));
$page->drawtext("$grand_total_green", 240, ($page->getHeight() - 195));
$page->drawtext("$grand_total_yellow", 330, ($page->getHeight() - 195));
$page->drawtext("$grand_total_zesty_lemonade", 420, ($page->getHeight() - 195));
$page->drawtext("$grand_total_cashewdream", 500, ($page->getHeight() - 195));
$page->drawLine(70, (($height - 140 * $counter) + 12), ($width - 70), (($height - 140 * $counter) + 12));
header('Content-type: application/pdf');
$pdfData = $pdf->render();
echo $pdfData;
?>