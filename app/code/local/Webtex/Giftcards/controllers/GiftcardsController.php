<?php

class Webtex_Giftcards_GiftcardsController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->_redirect('*/*/balance');
    }

    public function balanceAction()
    {
        if (!Mage::helper('customer')->isLoggedIn()) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    public function printAction()
    {
        if (!Mage::helper('customer')->isLoggedIn()) {
            Mage::getSingleton('customer/session')->addError("To print gift card  you need to be logged in");
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }

        if (($cardId = $this->getRequest()->getParam('id')) > 0) {
            $this->loadLayout('print');
            $this->renderLayout();
        } else {
            $this->_redirect('/');
        }
    }

    public function applyAction()
    {
        if (!Mage::helper('customer')->isLoggedIn()) {
            Mage::getSingleton('customer/session')->addError(
                $this->__('To redeem your gift card you need to be logged in.')
            );
			Mage::getSingleton('customer/session')->authenticate($this);
			return;
		}

        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $giftcardCode = trim((string) $this->getRequest()->getParam('giftcard_code'));
        $card = Mage::getModel('giftcards/giftcards')->load($giftcardCode, 'card_code');

    	if ($card->getId() && $card->getCardStatus() == 2) {
            $card->activateCardForCustomer($customerId);
            Mage::getSingleton('core/session')->addSuccess(
            	$this->__('Gift Card "%s" was applied.', Mage::helper('core')->escapeHtml($giftcardCode))
            );
    	} else {
            Mage::getSingleton('core/session')->addError(
            	$this->__('Gift Card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftcardCode))
            );
    	}

        $this->_redirect('*/*/balance');
    }
}
