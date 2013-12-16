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
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 18);
$page->drawText('Order for Shipment on 30 Jan 2013',160,($page->getHeight()-90));
$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
$style->setFont($font,12);
$page->setStyle($style);



$page->drawText('Total Numbers of Orders by Region',($width-520),($page->getHeight()-120));
$items=array(array('Auckland',8),
		array('Hamilton',3 ),
		array('Tauranga',3),
		array('Wellington',4));
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

$page->drawLine(($width-70), ($page->getHeight()-130), ($width-70), ($page->getHeight()-212));
$page->drawLine(($width-140), ($page->getHeight()-130), ($width-140), ($page->getHeight()-212));
$page->drawLine(($width-525), ($page->getHeight()-130), ($width-525), ($page->getHeight()-212));
/*for($i=0;$i<=$items_count;$i++)
{
for($j=0;$j<=1;$j++){

$page->drawText($items[$i][$j],($width-500),($height-160*$k));
$page->drawText($items[$i][$j],($width-200),($height-160*$k));
 }
  //$linewidth+=
 $page->drawLine(70,(($height-160*$k)+10),($width-70),(($height-160*$k)+10));
 $k+=0.2;
}*/
for($i=0;$i<=$items_count;$i++)
{

$page->drawText($items[$i][0],($width-520),($height-160*$k));
$page->drawText($items[$i][1],($width-80),($height-160*$k));
   //$linewidth+=
 $page->drawLine(70,(($height-160*$k)+12),($width-70),(($height-160*$k)+12));
 $k+=0.1;
}
$page->drawText('Note: Each order follows on a single page which is also used as the packing slip',($width-520),($height-160*$k));

$page->drawText('<dev note: orders are sorted first by region, then order number. Each order is printed on a separate',($width-520),($height-160*($k+0.2)));
$page->drawText('page. The summary page (this page) is printed first as the cover page>',($width-520),($height-160*($k+0.3)));

$pdf->pages[] = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
$page=$pdf->pages[1]; // this will get reference to the first page.
$page->setStyle($style);

$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 12);

$productitem=array('3 x Pure Green','1 x Yellow Hit','1 x Zesty Lemonade','1 x Cashew Dream');
$radius=2;
$i=1;
$j=1;
$page->drawText('Order #: 1000012 ',250,($page->getHeight()-80));
$page->drawText('Ship to:',80,($page->getHeight()-120));
$page->drawText('Phone: 021 123 4567',340,($page->getHeight()-120));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 12);
$page->drawText('John Smith',80,($page->getHeight()-140));
$page->drawText('123 Queen St',80,($page->getHeight()-160));
$page->drawText('Auckland, 1000',80,($page->getHeight()-180));
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
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 12);
$page->drawLine(80,($page->getHeight()-225),540,($page->getHeight()-225));
$page->drawText('1',110,($page->getHeight()-240));
$page->drawText('Basic Cleanse' ,140,($page->getHeight()-240));
foreach($productitem as $pitem)
{
$circ=$page->drawCircle(160,(($height-250*$i)),$radius);
$page->drawText($pitem,170,(($height-255*$i)));
$i+=0.05;
}
$page->drawText('30 Jan 2013 ',400,($page->getHeight()-240));
$page->drawLine(80,($page->getHeight()-305),540,($page->getHeight()-305));
$page->drawText('1',110,($page->getHeight()-320));
$page->drawText('Cooler Bag ',140,($page->getHeight()-320));
$page->drawText('30 Jan 2013 ',400,($page->getHeight()-320));
$page->drawLine(80,($page->getHeight()-325),540,($page->getHeight()-325));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 12);
$page->drawText('Special Instructions:',100,($page->getHeight()-420));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 12);
$page->drawText('<dev note: print special instructions from order (comments) here, if any>',100,($page->getHeight()-440));

$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 12);
$page->drawText('Food Allergies: ',100,($page->getHeight()-460));
$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 12);
$page->drawText('Yes|No (dev note: as per the customer selection on check out) ',100,($page->getHeight()-480));
$pdf->render();
$pdf->save('orderpdf/Orders for Shipment on Date.pdf');
?>