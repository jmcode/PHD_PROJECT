<?php

class Webtex_Giftcards_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * Process saving gift card product
     *
     * @param $observer
     */
    public function catalogProductSaveBefore($observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getTypeId() == 'giftcards') {
        	$product->setRequiredOptions('1');
        }
    }

    /**
     * Process saving order after user place order
     * Creates gift cards and charge off discount amount (only cards part) from user's balance
     *
     * @param $observer
     */
    public function checkoutTypeOnepageSaveOrderAfter($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $order->getQuote();
        try {
            /* Create cards if its present in order */
            foreach ($quote->getAllVisibleItems() as $item) {
                if ($item->getProduct()->getTypeId() == 'giftcards'){
                    $options = $item->getProduct()->getCustomOptions();
                    $optionsDataMap = array(
                        'card_type',
                        'mail_to',
                        'mail_to_email',
                        'mail_from',
                        'mail_message',
                        'offline_country',
                        'offline_state',
                        'offline_city',
                        'offline_street',
                        'offline_zip',
                        'offline_phone',
                    );
                    $data = array();
                    foreach ($optionsDataMap as $field) {
                        if (isset($options[$field])) {
                            $data[$field] = $options[$field]->getValue();
                        }
                    }
                    $data['card_amount'] = $item->getBasePrice();
                    $data['card_status'] = 0;
                    $data['order_id'] = $order->getId();

                    for ($i=0; $i<$item->getQty(); $i++) {
                        $model = Mage::getModel('giftcards/giftcards');
                        $model->setData($data);
			if (in_array($order->getState(), array('complete'))) {
				$model->setCardStatus(2);
			        $model->send();
			}
                        $model->save();
                    }

                }
            }

            if ($quote->getUseGiftcards()) {
                $cards = Mage::getModel('giftcards/giftcards')->getCollection()
                    ->addFieldToFilter('customer_id', $quote->getCustomerId())
                    ->addFieldToFilter('card_status', 1);
                $value = $quote->getGiftcardsDiscount();
                foreach ($cards as $card) {
                    $useAmount = min($card->getCardBalance(), $value);
                    if ($useAmount > 0) {
                        $value -= $card->getCardBalance();
                        $card->setCardBalance($card->getCardBalance() - $useAmount);
                        $card->save();
                    }
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($order, $e->getMessage());
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
        }
    }

    /**
     * Process order cancel
     * Adds discounted amount back to user's balance (whole part?)
     *
     * @param $observer
     */
    public function salesOrderCancelAfter($observer)
    {
   		$order = $observer->getEvent()->getOrder();

        if ($order->getDiscountDescription() == "Gift Card") {
            $cards = Mage::getModel('giftcards/giftcards')->getCollection()
                ->addFieldToFilter('customer_id', $order->getCustomerId())
                ->addFieldToFilter('card_status', 1);

            $discount = -$order->getBaseDiscountAmount();
            foreach ($cards as $card) {
                if ($discount > 0) {
                    $value = $discount - ($card->getCardAmount() - $card->getCardBalance());
                    if ($value >= 0) {
                        $value = $card->getCardAmount() - $card->getCardBalance();
                        $discount = $discount - $value;
                    } else {
                        $value = $discount;
                        $discount = 0;
                    }
                    $card->setCardBalance($value + $card->getCardBalance());
                    $card->save();
                }
            }
        }
   	}

    /**
     * Process order saving
     * Send cards emails on order complete
     *
     * @param $observer
     */
    public function salesOrderSaveAfter($observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (in_array($order->getState(), array('complete'))) {
            $cards = Mage::getModel('giftcards/giftcards')->getCollection()
                ->addFieldToFilter('order_id', $order->getId());
            foreach ($cards as $card) {
                $card->setCardStatus(2)->save();
                $card->send();
            }
        }
    }

    public function checkPriceIsZero($observer)
    {
        $block = $observer->getBlock();


        if(get_class($block) === 'Mage_Catalog_Block_Product_Price')
        {
            $product = $block->getProduct();
            if($product->getTypeId() === 'giftcards')
            {
                if($product->getPrice() == 0)
                {
                    $observer->getTransport()->setHtml('&nbsp');
                }
            }

        }
    }
}