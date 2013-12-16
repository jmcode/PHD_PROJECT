<?php

class Webtex_Giftcards_Model_Product_Price extends Mage_Catalog_Model_Product_Type_Price
{
    /**
     * Apply gift card amount to price
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $qty
     * @param double $finalPrice
     * @return double
     */
    protected function _applyOptionsPrice($product, $qty, $finalPrice)
    {
        if ($product->getCustomOption('card_amount')) {
            $amount = $product->getCustomOption('card_amount')->getValue();
            if ($product->getCustomOption('card_currency')) {
                $cardCurrency = Mage::getModel('directory/currency')->load($product->getCustomOption('card_currency')->getValue());
                $baseCurrency = Mage::app()->getStore()->getBaseCurrency();
                if ($baseCurrency->getRate($cardCurrency)) {
                    $amount = $amount / $baseCurrency->getRate($cardCurrency);
                }
            }
            $finalPrice += $amount;
        }
        return $finalPrice;
    }
}