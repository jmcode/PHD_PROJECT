<?php

class Webshopapps_Timegrid_Block_Adminhtml_Timegrid_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'timegrid';
        $this->_controller = 'adminhtml_timegrid';

        $this->_updateButton('save', 'label', Mage::helper('timegrid')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('timegrid')->__('Delete'));

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('timegrid_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'timegrid_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'timegrid_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('timegrid_data') && Mage::registry('timegrid_data')->getId() ) {
            return Mage::helper('timegrid')->__("Edit Weekly Prices '%s'", $this->htmlEscape(Mage::registry('timegrid_data')->getWeekCom()));  //TODO KAREN
        } else {
            return Mage::helper('timegrid')->__('Add Weekly Prices');
        }
    }
}