<?php
/**
 * Yireo DeleteAnyOrder for Magento 
 *
 * @package     Yireo_DeleteAnyOrder
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright (c) 2012 Yireo (http://www.yireo.com/)
 * @license     Open Software License
 */

/**
 * DeleteAnyOrder admin controller
 *
 * @category   DeleteAnyOrder
 * @package    Yireo_DeleteAnyOrder
 */
class Yireo_DeleteAnyOrder_IndexController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Common method
     *
     * @access protected
     * @param null
     * @return Yireo_DeleteAnyOrder_IndexController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/tools/deleteanyorder')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('System'), Mage::helper('adminhtml')->__('System'))
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Tools'), Mage::helper('adminhtml')->__('Tools'))
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Delete Any Order'), Mage::helper('adminhtml')->__('Delete Any Order'))
        ;
        return $this;
    }

    /**
     * Overview page
     *
     * @access public
     * @param null
     * @return null
     */
    public function indexAction()
    {
        Mage::getModel('adminhtml/session')->addError($this->__('Make sure you have a valid backup before continuing'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('deleteanyorder/overview'))
            ->renderLayout();
    }

    /**
     * Alias for overview
     *
     * @access public
     * @param null
     * @return null
     */
    public function gridAction()
    {
        $this->indexAction();
    }

    /**
     * Confirmation page
     *
     * @access public
     * @param null
     * @return null
     */
    public function confirmAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('deleteanyorder/confirm'))
            ->renderLayout();
    }

    /**
     * Delete action
     *
     * @access public
     * @param null
     * @return null
     */
    public function deleteAction()
    {
        // Delete the orders
        $order_ids = $this->getRequest()->getParam('order_ids', 0);
        $count = array('true' => 0, 'false' => 0);

        foreach($order_ids as $order_id) {
            if(Mage::getModel('deleteanyorder/order')->delete($order_id) == true) {
                $count['true']++;
            } else {
                $count['false']++;
            }
        }

        if($count['true'] > 0) {
            Mage::getModel('adminhtml/session')->addNotice($this->__('Deleted %s orders succesfully', $count['true']));
        }

        if($count['false'] > 0) {
            Mage::getModel('adminhtml/session')->addNotice($this->__('Unable to delete %s orders', $count['false']));
        }

        // Redirect
        $this->_redirect('deleteanyorder/index/index');
    }

    /**
     * Analyze page
     *
     * @access public
     * @param null
     * @return null
     */
    public function analyzeAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('deleteanyorder/analyze'))
            ->renderLayout();
    }

    /**
     * Clean-up page
     *
     * @access public
     * @param null
     * @return null
     */
    public function cleanupAction()
    {
        // Analyze and delete all left-overs
        Mage::getModel('deleteanyorder/database')->cleanup();

        // Redirect
        $this->_redirect('deleteanyorder/index/analyze');
    }
}
