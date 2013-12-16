<?php

class Webtex_Giftcards_Helper_Data extends Mage_Core_Helper_Data
{
    public function getCustomerBalance($customerId = 0)
    {
        $balance = 0;

        if ($customerId) {
            $cards = Mage::getModel('giftcards/giftcards')->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('card_status', 1);
            foreach ($cards as $card) {
                $balance += $card->getCardBalance();
            }
        }

        return $balance;
    }
    
    public function isShowEmailType()
    {
        return Mage::getStoreConfigFlag('giftcards/default/card_type_email');
    }

    public function isShowPrintType()
    {
        return Mage::getStoreConfigFlag('giftcards/default/card_type_print');
    }

    public function isShowOfflineType()
    {
        return Mage::getStoreConfigFlag('giftcards/default/card_type_offline');
    }

    public function isUseDefaultPicture()
    {
        return Mage::getStoreConfigFlag('giftcards/email/card_picture');
    }
}
