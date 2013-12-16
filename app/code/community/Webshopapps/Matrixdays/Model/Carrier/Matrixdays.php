<?php
/* Matrixdays
 *
 * @category   Webshopapps
 * @package    Webshopapps_matrixdays
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */




class Webshopapps_Matrixdays_Model_Carrier_Matrixdays
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'matrixdays';
    protected $_default_condition_name = 'per_package';
    protected $_modName = 'Webshopapps_Matrixdays';
    protected $_conditionNames = array();
    private $_options;


    public function __construct()
    {
        parent::__construct();
        foreach ($this->getCode('condition_name') as $k=>$v) {
            $this->_conditionNames[] = $k;
        }
    }

    /**
     * Enter description here...
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {

        //zend_debug::dump('collectRates','Webshopapps_Matrixdays_Model_Carrier_Matrixdays');


        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $this->_options = explode(',',Mage::getStoreConfig("carriers/matrixdays/ship_options"));

        //zend_debug::dump($this->_options,'Webshopapps_Matrixdays_Model_Carrier_Matrixdays');


        $request->setConditionName($this->getConfigData('condition_name') ? $this->getConfigData('condition_name') : $this->_default_condition_name);
        	$result = Mage::getModel('matrixdays_shipping/rate_result');

        $freeBoxes = 0;
        $found=false;
        $total=0;

     	try {
            //zend_debug::dump('BEFORE FOREACH','Webshopapps_Matrixdays_Model_Carrier_Matrixdays');

	        foreach ($request->getAllItems() as $item) {

                //zend_debug::dump($item->debug(),'ITEM');

	        	if ($item->getFreeShipping() && $item->getProductType()!= Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL ) {
	                    $freeBoxes+=$item->getQty();
	            }
	            if ($item->getProductType() != Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL &&
	                 $item->getProductType() != 'downloadable') {
	                    $total+= $item->getBaseRowTotal();
	                    $found=true;
	           	}
	        }

            //zend_debug::dump('AFTER FOREACH','Webshopapps_Matrixdays_Model_Carrier_Matrixdays');

        } catch (Exception $e) {
        	// this is really bad programmtically but we are going to ignore this, as in some cases there wont be
        	// anything in getAllItems.
        }

        //zend_debug::dump($total,'Webshopapps_Matrixdays_Model_Carrier_Matrixdays TOTAL');
        //zend_debug::dump($found,'Webshopapps_Matrixdays_Model_Carrier_Matrixdays FOUND');

        if ($found && in_array('remove_virtual',$this->_options)) {
        	// this fixes bug in Magento where package value is not set correctly, but at expense of sacrificing discounts
        	$request->setPackageValue($total);
        }
        //zend_debug::dump($freeBoxes,'Webshopapps_Matrixdays_Model_Carrier_Matrixdays freeboxes');

        $this->setFreeBoxes($freeBoxes);
        $freeFound=false;


        if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {

            //zend_debug::dump('FREE SHIPPING CASE','Webshopapps_Matrixdays_Model_Carrier_Matrixdays');
		  	$method = Mage::getModel('shipping/rate_result_method');
			$method->setCarrier('matrixdays');
			$method->setCarrierTitle($this->getConfigData('title'));
			$method->setMethod(strtolower('matrixdays_'.$this->getConfigData('free_shipping_text')));
			$method->setPrice('0.00');
			$method->setMethodTitle($this->getConfigData('free_shipping_text'));
			$result->append($method);
			$freeFound=true;
		}

     	$earliestExpectedDelivery=-1;
		$ratearray = $this->getRate($request,$earliestExpectedDelivery);

        //zend_debug::dump($ratearray,'Webshopapps_Matrixdays_Model_Carrier_Matrixdays RATES ARRAY');


     	if (empty($ratearray)) {
     		if (!($freeFound) && $this->getConfigData('specificerrmsg')!='') {
	            $error = Mage::getModel('shipping/rate_result_error');
	            $error->setCarrier('matrixdays');
	            $error->setCarrierTitle($this->getConfigData('title'));
	            //$error->setErrorMessage($errorText);
	            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
	            $result->append($error);
     		}
            //zend_debug::dump('NO RESULT','Webshopapps_Matrixdays_Model_Carrier_Matrixdays');

			return $result;

     	}
     	$max_shipping_cost=$this->getConfigData('max_shipping_cost');

     	if (Mage::helper('matrixdays')->getUsingGrid()) {
     		// grid format
     		foreach ($ratearray as $rate)
			{
				if (empty($rate)) {
					continue;
				}
			  	$method = Mage::getModel('shipping/rate_result_method');
				$method->setCarrier('matrixdays');
				$method->setCarrierTitle($this->getConfigData('title'));
				$price=$rate['price'];

				if (!empty($max_shipping_cost) && $max_shipping_cost>0) {
					if ($price>$max_shipping_cost) {
						$price=$max_shipping_cost;
					}
				}
				if (!in_array('apply_handling',$this->_options) && $price==0) {
					$shippingPrice = $price;
				} else {
					$shippingPrice = $this->getFinalPriceWithHandlingFee($price);
				}
				/*	if ($price==0  && $this->getConfigData('zero_shipping_text')!='') {
	       	   			$modifiedName=preg_replace('/&|;| /',"_",$this->getConfigData('zero_shipping_text'));
						$method->setMethodTitle($this->getConfigData('zero_shipping_text'));
					} else {   TODO What to do here*/
	       	   			$modifiedName=preg_replace('/&|;| /',"_",$rate['method_name']);
						$method->setMethodTitle(Mage::helper('shipping')->__($rate['delivery_type']));

				//	}
				if ($earliestExpectedDelivery!=-1) {
					$method->setEarliest($earliestExpectedDelivery);
				}

				if (array_key_exists('dispatch_date', $rate)) {  // doesnt work with grid
					$method->setDispatchDate($rate['dispatch_date']);
				} else {
					$method->setDispatchDate($rate['dispatch']);
				}

				if (array_key_exists('expected_delivery',$rate)) {
					$method->setExpectedDelivery($rate['expected_delivery']);
				}

				if (array_key_exists('dispatch_date', $rate)) {
					$method->setMethod(strtolower($rate['code']));
				} else {
					$method->setMethod(strtolower($modifiedName));
				}


				$method->setMethodDescription($rate['method_description']);
				$method->setPrice($shippingPrice);
				$method->setCost($rate['price']);
				$method->setDeliveryType(Mage::helper('shipping')->__($rate['delivery_type']));

				$result->append($method);
			}

     	} else {

		    foreach ($ratearray as $rate)
			{
			   if (!empty($rate) && $rate['price'] >= 0) {
				  	$method = Mage::getModel('shipping/rate_result_method');

					$method->setCarrier('matrixdays');
					$method->setCarrierTitle($this->getConfigData('title'));

					$price=$rate['price'];
					if (!empty($max_shipping_cost) && $max_shipping_cost>0) {
						if ($price>$max_shipping_cost) {
							$price=$max_shipping_cost;
						}
					}

					if (!in_array('exlcude_expected_delivery',$this->_options)){
					 	$fullTextDeliveryType=$rate['delivery_type']." (".$rate['expected_delivery'].")";
					}
					else{
						$fullTextDeliveryType=$rate['delivery_type'];
					}

					if (array_key_exists('append', $rate)) {
						$fullTextDeliveryType.=' '.$rate['append'];
						$method->setDeliveryNotes($rate['append']);
					}
					if (!in_array('apply_handling',$this->_options) && $price==0) {
						$shippingPrice = $price;
					} else {
						$shippingPrice = $this->getFinalPriceWithHandlingFee($price);
					}
					if ($price==0  && $this->getConfigData('zero_shipping_text')!='') {
	       	   			$modifiedName=preg_replace('/&|;| /',"_",$this->getConfigData('zero_shipping_text'));
						$method->setMethodTitle($this->getConfigData('zero_shipping_text'));
					} else {
	       	   			$modifiedName=preg_replace('/&|;| /',"_",$rate['method_name']);
						$method->setMethodTitle(Mage::helper('shipping')->__($fullTextDeliveryType));
					}

					$method->setDispatchDate($rate['dispatch']);
					if (array_key_exists('expected_delivery',$rate)) {
						$method->setExpectedDelivery($rate['expected_delivery']);
					}

					$method->setMethod(strtolower($modifiedName));

					$method->setPrice($shippingPrice);
					$method->setCost($rate['cost']);
					$method->setDeliveryType($fullTextDeliveryType);
					$method->setDeliveryDescription($rate['delivery_type']);

					$result->append($method);
				}
			}
     	}

        return $result;
    }

    public function getCode($type, $code='')
    {
        $request = Mage::getModel('shipping/rate_request');
        $codes = array(

            'condition_name'=>array(
                'per_item_bare' => Mage::helper('shipping')->__('Per Item Bare Totalling'),
                'per_item_surcharge' => Mage::helper('shipping')->__('Per Item Surcharge Totalling'),
        		'per_item' => Mage::helper('shipping')->__('Per Item Totalling'),
                'per_product' => Mage::helper('shipping')->__('Per Product Totalling'),
          		'per_product_bare' => Mage::helper('shipping')->__('Per Product Bare Totalling'),
        		'per_package'  => Mage::helper('shipping')->__('Per Package Totalling'),
            	'highest'  => Mage::helper('shipping')->__('Highest Price Totalling'),
            	'lowest'  => Mage::helper('shipping')->__('Lowest Price Totalling'),
        ),

            'condition_name_short'=>array(
                'per_item_bare' => Mage::helper('shipping')->__('Per Item Bare Totalling'),
                'per_item_surcharge' => Mage::helper('shipping')->__('Per Item Surcharge Totalling'),
                'per_item' => Mage::helper('shipping')->__('Per Item Totalling'),
                'per_product' => Mage::helper('shipping')->__('Per Product Totalling'),
               	'per_product_bare' => Mage::helper('shipping')->__('Per Product Bare Totalling'),
            	'per_package'  => Mage::helper('shipping')->__('Per Package Totalling'),
            	'highest'  => Mage::helper('shipping')->__('Highest Price Totalling'),
        		'lowest'  => Mage::helper('shipping')->__('Lowest Price Totalling'),
        ),

        'parent_group'=>array(
            	'child'  => Mage::helper('shipping')->__('Default(Child) Shipping Group'),
            	'both'  => Mage::helper('shipping')->__('Parent Shipping Group'),
        		'configurable'  => Mage::helper('shipping')->__('Configurable Parent, Bundle Child'),
        		'bundle'  => Mage::helper('shipping')->__('Configurable Child, Bundle Parent'),
        ),
        'postcode_filtering'=>array(
            	'uk'  => Mage::helper('shipping')->__('UK'),
            	'numeric'  => Mage::helper('shipping')->__('Numerical Ranges'),
        		'both'  => Mage::helper('shipping')->__('Both'),
        		'none'  => Mage::helper('shipping')->__('None/Pattern Matching'),
        ),
        'shipoptions'=>array(
        		'append_star_rates'  		=> Mage::helper('shipping')->__('Append * shipping group rates'),
        		'apply_handling'  			=> Mage::helper('shipping')->__('Apply handling fee on zero shipping'),
        		'display_matrix'  			=> Mage::helper('shipping')->__('Display as Ajax calendar'),
             	'exclude_shipping_method' 	=> Mage::helper('shipping')->__('Dont show shipping method'),
        		'remove_virtual'  			=> Mage::helper('shipping')->__('Exclude virtual from cart price'),
        		'filter_subtotal'  			=> Mage::helper('shipping')->__('Filter on subtotal price'),
                'filter_weight'  			=> Mage::helper('shipping')->__('Filter on subtotal weight'),
                'filter_volume'  			=> Mage::helper('shipping')->__('Filter on subtotal volume'),
           		'show_whole_prices'  		=> Mage::helper('shipping')->__('Round rates in calendar to nearest whole number'),
             	'show_information'  		=> Mage::helper('shipping')->__('Show information text'),
        		'exlcude_expected_delivery' => Mage::helper('shipping')->__('Dont show expected delivery date'),
        		'use_discounted'  			=> Mage::helper('shipping')->__('Use discounted price'),
        		'group_text'  				=> Mage::helper('shipping')->__('Use text based shipping group'),
        		'NONE'  					=> Mage::helper('shipping')->__('N/A'),
        ),
         'days'=>array(
                '1'    => Mage::helper('matrixdays')->__('MONDAY'),
                '2'   => Mage::helper('matrixdays')->__('TUESDAY'),
                '3'    => Mage::helper('matrixdays')->__('WEDNESDAY'),
                '4'   => Mage::helper('matrixdays')->__('THURSDAY'),
                '5'  => Mage::helper('matrixdays')->__('FRIDAY'),
                '6'    => Mage::helper('matrixdays')->__('SATURDAY'),
                '7'   => Mage::helper('matrixdays')->__('SUNDAY'),
                '99'   => Mage::helper('matrixdays')->__('NONE'),

            ),
            'short_days'=>array(
                '1'    => Mage::helper('matrixdays')->__('Mon'),
                '2'   => Mage::helper('matrixdays')->__('Tue'),
                '3'    => Mage::helper('matrixdays')->__('Wed'),
                '4'   => Mage::helper('matrixdays')->__('Thur'),
                '5'  => Mage::helper('matrixdays')->__('Fri'),
                '6'    => Mage::helper('matrixdays')->__('Sat'),
                '7'   => Mage::helper('matrixdays')->__('Sun'),
            ),
          'date_format'=>array(
                '1'    	=> 'd-m-Y',
                '2'    	=> 'm/d/Y',
          		'3'		=> 'D d-m-Y',
              //  '3'    	=> 'j, n, Y',  //TODO Get these working with strtotime
              //  '4'   	=> 'm.d.y',
              //  '5'  	=> 'm/d/Y',
              //  '6'    	=> 'd.m.y',
              //  '7'   	=> 'F j, Y',
            //	'8'   	=> 'D M j Y',
            ),
            'timeslots'=>array(
            	'1'		=> '1',
            	'2'		=> '2',
                '3'		=> '3',
                '4'		=> '4',
            	'5'		=> '5',
            	'6'		=> '6',
            ),
            'num_of_weeks'=>array(
            	'1'		=>  '1',
				'2'		=>  '2',
				'3'		=>  '3',
				'4' 	=>	'4',
				'5'	 	=>	'5',
				'6' 	=>	'6',
				'7'	 	=>	'7',
				'8' 	=>	'8',
				'9' 	=>	'9',
				'10'	=>	'10',
				'11'	=>	'11',
				'12'	=>	'12',
				'13'	=>	'13',
				'14'	=>	'14',
				'15'	=>	'15',
				'16'	=>	'16',
				'17'	=>	'17',
				'18'	=>	'18',
				'19'	=>	'19',
				'20'	=>	'20',
				'21'	=>	'21',
				'22'	=>	'22',
				'23'	=>	'23',
				'24'	=>	'24',
				'25'	=>	'25',
				'26'	=>	'26',
				'27'	=>	'27',
				'28'	=>	'28',
				'29'	=>	'29',
				'30'	=>	'30',
				'31'	=>	'31',
            	'32'	=>	'32',
				'33'	=>	'33',
				'34'	=>	'34',
				'35'	=>	'35',
				'36'	=>	'36',
				'37'	=>	'37',
				'38'	=>	'38',
				'39'	=>	'39',
				'40'	=>	'40',
				'41'	=>	'41',
				'42'	=>	'42',
				'43'	=>	'43',
				'44'	=>	'44',
				'45'	=>	'45',
				'46'	=>	'46',
				'47'	=>	'47',
				'48'	=>	'48',
            	'49'	=>	'49',
				'50'	=>	'50',
				'51'	=>	'51',
				'52'	=>	'52'
            ),
            'num_of_dates_at_checkout'=>array(
            	'1'		=>  '1',
				'2'		=>  '2',
				'3'		=>  '3',
				'4' 	=>	'4',
				'5'	 	=>	'5',
				'6' 	=>	'6',
				'7'	 	=>	'7',
				'8' 	=>	'8',
				'9' 	=>	'9',
				'10'	=>	'10',
				'11'	=>	'11',
				'12'	=>	'12',
				'13'	=>	'13',
				'14'	=>	'14',
				'15'	=>	'15',
				'16'	=>	'16',
				'17'	=>	'17',
				'18'	=>	'18',
				'19'	=>	'19',
				'20'	=>	'20',
				'21'	=>	'21',
				'22'	=>	'22',
				'23'	=>	'23',
				'24'	=>	'24',
				'25'	=>	'25',
				'26'	=>	'26',
				'27'	=>	'27',
				'28'	=>	'28',
				'29'	=>	'29',
				'30'	=>	'30',
                '31'	=>	'31',
                '32'	=>	'32',
                '33'	=>	'33',
                '34'	=>	'34',
                '35'	=>	'35',
                '40'	=>	'40',
                '45'	=>	'45',
                '50'	=>	'50',
                '55'	=>	'55',
                '60'	=>	'60',
            )

        );

        if (!isset($codes[$type])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Product Matrix code type: %s', $type));
        }

        if (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Product Matrix  code for type %s: %s', $type, $code));
        }

        return $codes[$type][$code];
    }

    public function getRate(Mage_Shipping_Model_Rate_Request $request,&$earliestExpectedDelivery)
    {
        return Mage::getResourceModel('matrixdays_shipping/carrier_matrixdays')->getNewRate($request,$earliestExpectedDelivery);
    }

 /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
       $collection = Mage::getResourceModel('matrixdays_shipping/carrier_matrixdays_collection');
       $collection = $collection->setDistinctDeliveryTypeFilter();
       $collection->load();
       $allowedMethods=array();
       $deliveryTypes=array();
       foreach ($collection->getItems() as $item) {
       	   $newDelType=preg_replace('/&|;| /',"_",$item['delivery_type']);
       	   $deliveryTypes[]=$newDelType;
       	   $allowedMethods[$newDelType] = $item['delivery_type'];
       }
       return $allowedMethods;
    }


}
