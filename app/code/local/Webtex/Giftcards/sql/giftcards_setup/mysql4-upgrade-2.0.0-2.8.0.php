<?php
$installer = $this;
$installer->startSetup();

$installer->addAttribute('catalog_product', 'wts_gc_additional_prices', array(
    'group' => 'Prices',
    'sort_order' => 2,
    'backend' => 'webtex_giftcards_model_product_additionalprice',
    'type' => 'text',
    'input_renderer' => 'Webtex_Giftcards_Block_Adminhtml_Catalog_Product_Form_Additionalprice',
    'label' => 'Preset prices',
    'note' => 'Input here additional prices to be selected in dropdown on frontend',
    'input' => 'text',
    'required' =>false,
    'visible' =>true,
    'visible_on_front' => false,
    'apply_to' => Webtex_Giftcards_Model_Product_Type::TYPE_GIFTCARDS_PRODUCT
));

$this->endSetup();