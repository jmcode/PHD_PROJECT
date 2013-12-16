<?php
/**
 * @category   ASPerience
 * @package    Asperience_DeleteAllOrders
 * @author     ASPerience - www.asperience.fr
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'Mage/Adminhtml/controllers/Sales/OrderController.php';

class Asperience_DeleteAllOrders_IndexController extends Mage_Adminhtml_Sales_OrderController
{
	protected function _construct()
	{
		$this->setUsedModuleName('Asperience_DeleteAllOrders');
	}
    /**
     * Delete selected orders
     */
    public function indexAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
    	if (Mage::getStoreConfig(Asperience_DeleteAllOrders_Model_Order::XML_PATH_SALES_IS_ACTIVE)) {
	        $countDeleteOrder = 0;
	        $countDeleteInvoice = 0;
	        $countDeleteShipment = 0;
	        $countDeleteCreditmemo = 0;
	        
	        try {
		        foreach ($orderIds as $orderId) {
		        	$order = Mage::getModel('deleteallorders/order')->load($orderId);
		            if ($order->canDelete()) {
		            
			            if ($order->hasInvoices()) {
			            	$invoices = Mage::getResourceModel('sales/order_invoice_collection')->setOrderFilter($orderId)->load();
			            	foreach($invoices as $invoice){
			            		$invoice = Mage::getModel('sales/order_invoice')->load($invoice->getId());
			            		$invoice->delete();
			            		$countDeleteInvoice++;
			            	}
			            }
			            
			        	if ($order->hasShipments()) {
			            	$shipments = Mage::getResourceModel('sales/order_shipment_collection')->setOrderFilter($orderId)->load();
			            	foreach($shipments as $shipment){
			            		$shipment = Mage::getModel('sales/order_shipment')->load($shipment->getId());
			            		$shipment->delete();
			            		$countDeleteShipment++;
			            	}
			            }
			            
			        	if ($order->hasCreditmemos()) {
			            	$creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')->setOrderFilter($orderId)->load();
			            	foreach($creditmemos as $creditmemo){
			            		$creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemo->getId());
			            		$creditmemo->delete();
			            		$countDeleteCreditmemo++;
			            	}
			            }
			            
			            $order->delete();
			            $countDeleteOrder++;
			        }
		        }
		        
		        if ($countDeleteOrder > 0) {
		            $this->_getSession()->addSuccess($this->__('%s sale(s) order(s) was/were successfully deleted.', $countDeleteOrder));
			        if ($countDeleteInvoice > 0) {
			            $this->_getSession()->addSuccess($this->__('%s invoice(s) order(s) was/were successfully deleted.', $countDeleteInvoice));
			        }
			        if ($countDeleteShipment > 0) {
			            $this->_getSession()->addSuccess($this->__('%s shipment(s) order(s) was/were successfully deleted.', $countDeleteShipment));
			        }
		        	if ($countDeleteCreditmemo > 0) {
			            $this->_getSession()->addSuccess($this->__('%s credit memo(s) order(s) was/were successfully deleted.', $countDeleteCreditmemo));
			        }
		        } else {
		            $this->_getSession()->addError($this->__('Selected order(s) can not be deleted.'));
		        }
	        } catch (Exception $e){
	        	$this->_getSession()->addError($this->__('An error arose during the deletion. %s', $e));
	        }
        } else {
        	$this->_getSession()->addError($this->__('This feature was deactivated.'));
        }
	$this->_redirect('adminhtml/sales_order/', array());	
    }
}