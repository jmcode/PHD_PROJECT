<?php
/**
 * @author Alex Pape
 * @package TWC_CGHandle
 */
class TWC_CGHandle_Model_Observer
{
    public function addCustomerGroupHandle(Varien_Event_Observer $observer)
    {
        if (Mage::helper('customer')->isLoggedIn()) {
            /** @var $update Mage_Core_Model_Layout_Update */
            $update = $observer->getEvent()->getLayout()->getUpdate();
            $groupId = Mage::helper('customer')->getCustomer()->getGroupId();
            $groupName = Mage::getModel('customer/group')->load($groupId)->getCode();
            $update->addHandle('customer_group_' . str_replace(' ', '_', strtolower($groupName)));
        }

        return $this;
    }
}