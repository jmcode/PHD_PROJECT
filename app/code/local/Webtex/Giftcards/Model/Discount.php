<?php

class Webtex_Giftcards_Model_Discount extends Mage_SalesRule_Model_Quote_Discount
{
    /**
     * Collect gift card discount amount
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_SalesRule_Model_Quote_Discount
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        /* Apply standard sales rules */
        parent::collect($address);

        $address->getQuote()->setUseGiftcards(false);
        if ($this->getGiftcardDiscountEnabled()) {
            $giftcardBalance = Mage::app()->getStore($address->getQuote()->getStoreId())->convertPrice($this->getAvailableGiftCardBalance());
            $baseGiftcardBalance = $this->getAvailableGiftCardBalance();

            /*Check if need to add shipping to giftcard*/
            if(Mage::getStoreConfig('giftcards/default/card_apply_to_shipping'))
            {
                $shippingAmount     = $address->getShippingAmountForDiscount();
                if ($shippingAmount!==null) {
                    $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
                } else {
                    $shippingAmount     = $address->getShippingAmount() ? $address->getShippingAmount() : 0;
                    $baseShippingAmount = $address->getBaseShippingAmount() ? $address->getShippingAmount() : 0;
                }
            }
            else
            {
                $shippingAmount = 0;
                $baseShippingAmount = 0;
            }

            /* Calculate discount */
            $this->_collectSubtotals($address);
            $subtotal = $this->_getSubtotal($address);
            $baseSubtotal = $this->_getBaseSubtotal($address);
            $giftcardsDiscount = min($subtotal+$shippingAmount, $giftcardBalance);
            $baseGiftcardsDiscount = min($baseSubtotal+$baseShippingAmount, $baseGiftcardBalance);

            /* Apply discount to items */
            $items = $this->_getAddressItems($address);
            $discount = $baseGiftcardsDiscount;
            foreach ($items as $item) {
                //Skipping child items to avoid double calculations
                if ($item->getParentItemId()) {
                    continue;
                }
                $quote = $item->getQuote();
                $basePrice = ($item->getDiscountCalculationPrice() !== null) ? $item->getBaseDiscountCalculationPrice() : $item->getBaseCalculationPrice();
                $price = ($item->getDiscountCalculationPrice() !== null) ? $item->getDiscountCalculationPrice() : $item->getCalculationPrice();
                if ($this->_subtotals['items_count'] <= 1) {
                    $discountAmount = $quote->getStore()->convertPrice($discount);
                    $baseDiscountAmount = min($basePrice * $item->getTotalQty(), $discount);
                } else {
                    $discountRate = $basePrice * $item->getTotalQty() / $baseSubtotal;
                    $baseDiscountAmount = $baseGiftcardsDiscount * $discountRate;
                    $discountAmount = $quote->getStore()->convertPrice($baseDiscountAmount);
                    $this->_subtotals['items_count']--;
                }
                $baseDiscountAmount = min($basePrice * $item->getTotalQty(), $baseDiscountAmount);
                $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);
                $discountAmount = min($price * $item->getTotalQty(), $discountAmount);
                $discountAmount = $quote->getStore()->roundPrice($discountAmount);

                $itemDiscountAmount = $item->getDiscountAmount();
                $itemBaseDiscountAmount = $item->getBaseDiscountAmount();

                $itemDiscountAmount     = min($itemDiscountAmount + $discountAmount, $price * $item->getTotalQty());
                $itemBaseDiscountAmount = min($itemBaseDiscountAmount + $baseDiscountAmount, $basePrice * $item->getTotalQty());
                
                $item->setDiscountAmount($itemDiscountAmount);
                $item->setBaseDiscountAmount($itemBaseDiscountAmount);
                $discount -= $baseDiscountAmount;
            }

            /* Apply discount */
            $this->_addAmount(-$giftcardsDiscount);
            $this->_addBaseAmount(-$baseGiftcardsDiscount);

            /* Append giftcard description */
            $descriptions = $address->getDiscountDescriptionArray();
            $descriptions[] = 'Gift Card';
            $address->setDiscountDescriptionArray($descriptions);
            $this->_calculator->prepareDescription($address);

            /* Save gift discount params */
            $address->getQuote()->setUseGiftcards(true);
            $address->getQuote()->setGiftcardsDiscount($address->getQuote()->getGiftcardsDiscount() + $baseGiftcardsDiscount);
        }

        return $this;
    }

    protected function _collectSubtotals(Mage_Sales_Model_Quote_Address $address)
    {
        $totalItems = 0;
        $totalItemsPrice = 0;
        $totalBaseItemsPrice = 0;
        $items = $this->_getAddressItems($address);
        foreach ($items as $item) {
            //Skipping child items to avoid double calculations
            if ($item->getParentItemId()) {
                continue;
            }
            $qty = $item->getTotalQty();
            $price = ($item->getDiscountCalculationPrice() !== null) ? $item->getDiscountCalculationPrice() : $item->getCalculationPrice();
            $basePrice = ($item->getDiscountCalculationPrice() !== null) ? $item->getBaseDiscountCalculationPrice() : $item->getBaseCalculationPrice();
            $totalItemsPrice += $price * $qty;
            $totalBaseItemsPrice += $basePrice * $qty;
            $totalItems++;
        }
        $this->_subtotals = array(
            'items_count' => $totalItems,
            'items_price' => $totalItemsPrice,
            'base_items_price' => $totalBaseItemsPrice,
        );
    }

    protected function _getSubtotal()
    {
        return $this->_subtotals['items_price'];
    }

    protected function _getBaseSubtotal()
    {
        return $this->_subtotals['base_items_price'];
    }

    protected function getGiftcardDiscountEnabled()
    {
        return (Mage::getSingleton('giftcards/session')->getActive() == '1');
    }

    protected function getAvailableGiftCardBalance()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            $customerId = Mage::getSingleton('adminhtml/session_quote')->getCustomerId();
        }else{
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        }

        return Mage::helper('giftcards')->getCustomerBalance($customerId);
    }

}
