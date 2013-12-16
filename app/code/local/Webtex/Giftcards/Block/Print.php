<?php

class Webtex_Giftcards_Block_Print extends Mage_Core_Block_Template
{
	public function getGiftcard() {
        if (($cardId = $this->getRequest()->getParam('id')) > 0) {
            $card = Mage::getModel('giftcards/giftcards')->load($cardId);
            $order = Mage::getModel('sales/order')->load($card->getOrderId());
            if ($card->getCardStatus() == 2 && $order->getCustomerId() == Mage::getSingleton('customer/session')->getCustomerId())
            {
                return $card;
            }
        }
		return false;
	}
}