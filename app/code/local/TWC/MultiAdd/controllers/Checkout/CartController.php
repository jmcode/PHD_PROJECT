<?php

require_once 'Mage/Checkout/controllers/CartController.php';

class TWC_MultiAdd_Checkout_CartController extends Mage_Checkout_CartController
{
    /**
     * Adding multiple products to shopping cart action
     * based on Mage_Checkout_CartController::addAction()
     * see also http://www.magentocommerce.com/boards/viewthread/8610/
     * and http://www.magentocommerce.com/wiki/how_to_overload_a_controller
     */
    public function addmultipleAction()
    {
        $productIds = $this->getRequest()->getParam('products');
        if (!is_array($productIds)) {
            $this->_goBack();
            return;
        }

        $cart = $this->_getCart();

        foreach($productIds as $productId) {
            try {
                $qty = $this->getRequest()->getParam('qty' . $productId, 0);
                if ($qty <= 0) continue; // nothing to add

                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
                $eventArgs = array(
                    'product' => $product,
                    'qty' => $qty,
                    'additional_ids' => array(),
                    'request' => $this->getRequest(),
                    'response' => $this->getResponse(),
                );
    
                Mage::dispatchEvent('checkout_cart_before_add', $eventArgs);
    
                $cart->addProduct($product, $qty);
    
                Mage::dispatchEvent('checkout_cart_after_add', $eventArgs);

                Mage::dispatchEvent('checkout_cart_add_product', array('product'=>$product));
    
                $message = $this->__('%s was successfully added to your shopping cart.', $product->getName());    
                Mage::getSingleton('checkout/session')->addSuccess($message);
            }
            catch (Mage_Core_Exception $e) {
                if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                    Mage::getSingleton('checkout/session')->addNotice($product->getName() . ': ' . $e->getMessage());
                }
                else {
                    Mage::getSingleton('checkout/session')->addError($product->getName() . ': ' . $e->getMessage());
                }
            }
            catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addException($e, $this->__('Cannot add %s to shopping cart', $product->getName()));
            }
        }
        $cart->save();
        $this->_getSession()->setCartWasUpdated(true);

        $this->_goBack();
    }
}