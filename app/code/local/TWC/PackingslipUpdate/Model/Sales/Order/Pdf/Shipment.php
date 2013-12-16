<?php
/**
 * Sales Order Shipment PDF model override
 * include Belvg Checkout fields 'delivery instruction' field in packing slip PDF
 *
 * @category   TWC
 * @package    TWC_PackingslipUpdate
 */
class TWC_PackingslipUpdate_Model_Sales_Order_Pdf_Shipment extends Mage_Sales_Model_Order_Pdf_Shipment
{
    /**
    * Return PDF document
    *
    * @param  array $shipments
    * @return Zend_Pdf
    */
    public function getPdf($shipments = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                Mage::app()->getLocale()->emulate($shipment->getStoreId());
                Mage::app()->setCurrentStore($shipment->getStoreId());
            }
            $page  = $this->newPage();
            $order = $shipment->getOrder();
            /* Add image */
            $this->insertLogo($page, $shipment->getStore());
            /* Add address */
            $this->insertAddress($page, $shipment->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $shipment,
                Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID, $order->getStoreId())
            );
            /* Add document text and number */
            $this->insertDocumentNumber(
                $page,
                Mage::helper('sales')->__('Packingslip # ') . $shipment->getIncrementId()
            );

            /* Get order attributes for Belvg checkoutfields extension */
            $orderFields = Mage::getModel('checkoutfields/orders')->loadByOrderId($order->getId());
            /* Get value for attribute id 146 - delivery instructions */
            if($orderFields){
                foreach($orderFields as $field){
                    if($field->getAttributeId() == '146'){
                        $instructions = $field->getValue();
                    }
                }
                /* Add instructions box to PDF - fixed height for box set to 25 */
                if($instructions){
                    $this->y += 7;
                    $this->_setFontRegular($page, 10);
                    $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
                    $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
                    $page->setLineWidth(0.5);
                    $page->drawRectangle(25, $this->y, 570, $this->y-20);
                    $start = $this->y - 20;

                    $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                    $this->_setFontBold($page, 10);
                    $page->drawText(Mage::helper('sales')->__('Delivery Instructions:'), 35, ($this->y - 14), 'UTF-8');
                    $this->y -= 35;

                    $page->setFillColor(Zend_Pdf_Color_Html::color('#FFFFFF'));
                    $page->drawRectangle(25, $start, 570, $start -50);

                    $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                    $this->_setFontRegular($page, 10);
                    foreach (Mage::helper('core/string')->str_split($instructions, 130, true, true) as $_value) {
                        $page->drawText(trim(strip_tags($_value)),
                            35,
                            $this->y,
                            'UTF-8');
                        $this->y -= 12;
                    }
                    //$page->drawText($instructions, 35, $this->y, 'UTF-8');
                    $this->y = $start - 60;
                }
            }
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($shipment->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
        }
        $this->_afterGetPdf();
        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        return $pdf;
    }
}