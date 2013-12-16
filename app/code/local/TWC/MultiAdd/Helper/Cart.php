<?php

class TWC_MultiAdd_Helper_Cart extends Mage_Core_Helper_Url
{
    /**
     * Return url to add multiple items to the cart
     * @return  url
     */
    public function getAddToCartUrl()
    {
        if ($currentCategory = Mage::registry('current_category')) {
            $continueShoppingUrl = $currentCategory->getUrl();
        } else {
            $continueShoppingUrl = $this->_getUrl('*/*/*', array('_current'=>true));
        }

        $params = array(
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => Mage::helper('core')->urlEncode($continueShoppingUrl)
        );

        if ($this->_getRequest()->getModuleName() == 'checkout'
            && $this->_getRequest()->getControllerName() == 'cart') {
            $params['in_cart'] = 1;
        }
        return $this->_getUrl('checkout/cart/addmultiple', $params);
    }
}
?>