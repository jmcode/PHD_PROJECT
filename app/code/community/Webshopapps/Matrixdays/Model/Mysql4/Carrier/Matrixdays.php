<?php
/* Matrixdays
 *
 * @category   Webshopapps
 * @package    Webshopapps_matrixdays
 * @copyright  Copyright (c) 2010 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


class Webshopapps_Matrixdays_Model_Mysql4_Carrier_Matrixdays extends Mage_Core_Model_Mysql4_Abstract
{

	private $_request;
	private $_zipSearchString;
	private $_table;
	private $_customerGroupCode;
	private $_starIncludeAll;
	private $_minusOne;
	private $_exclusionList;
	private $_structuredItems;
	private $_hasEmptyPackages;
	private $_prioritySet;
	private $_maxPriceSet;
	private $_freeShipping;
	private $_useParent;
	private $_postcodeFiltering;
	private $_ukFiltering;
	private $_shortUKPostcode='';
	private $_longUKPostcode='';
	private $_useBundleParent=false;
	private $_useConfigurableParent=true;
	private $_appendStarRates;
	private $_stockFound = false;
	private $_outofstock = false;
	private $_debug;
	private $_options;
	private $_ignoreAdditionalItemPrice;
	private $_defaultShipRate;
	private $_defaultSlots;
	private $_earliestCutoff='23:59';
	private $_usingGreaterVolLogic=false;
	private $_alwaysUseWeight = false;
	private $_greaterVolume = false;
	private $_volumeSwitch = false;

    protected function _construct()
    {
        $this->_init('shipping/matrixdays', 'pk');
    }

    public function getNewRate(Mage_Shipping_Model_Rate_Request $request,&$earliestExpectedDelivery)
    {
        //zend_debug::dump('getNewRate','Webshopapps_Matrixdays_Model_Mysql4_Carrier_Matrixdays');


    	Mage::helper('matrixdays')->resetStatics();
    	$this->_debug=Mage::helper('wsalogger')->isDebug('Webshopapps_Matrixdays');
    	$this->_hasEmptyPackages=false;
    	$this->_prioritySet = false;
    	$this->_ignoreAdditionalItemPrice=false;
        $read = $this->_getReadAdapter();
        $this->_maxPriceSet = false;
        $this->_freeShipping = $request->getFreeShipping();
        $postcodeFilter = Mage::getStoreConfig("carriers/matrixdays/postcode_filter");

        $this->_usingGreaterVolLogic=Mage::getStoreConfig('carriers/matrixdays/calculate_greater_volume');
        $this->_alwaysUseWeight = Mage::getStoreConfig('carriers/matrixdays/always_weight');
        $this->_volumeSwitch = Mage::getConfig()->getNode('matrixdays/specialvars/volume') == 1;

        $this->_options = explode(',',Mage::getStoreConfig("carriers/matrixdays/ship_options"));
        if ($this->_debug) {
			Mage::helper('wsacommon/log')->postNotice('matrixdays','Settings',$this->_options);
		}

        $postcode = Mage::helper('matrixdays')->zipCodeLength($request);

        $this->_table = Mage::getSingleton('core/resource')->getTableName('matrixdays_shipping/matrixdays');

        $this->_postcodeFiltering = $postcodeFilter;
        $this->_appendStarRates =  in_array('append_star_rates',$this->_options);
    	$this->_ukFiltering = false;

		if ($postcodeFilter == 'numeric') {
            //zend_debug::dump('CASE1','Webshopapps_Matrixdays_Model_Mysql4_Carrier_Matrixdays');
			#  Want to search for postcodes within a range
			 $this->_zipSearchString = $read->quoteInto(" AND dest_zip<=? ", $postcode).
								$read->quoteInto(" AND dest_zip_to>=? )", $postcode);
		} else if ($postcodeFilter == 'uk' && strlen($postcode)>4) {
            //zend_debug::dump('CASE2','Webshopapps_Matrixdays_Model_Mysql4_Carrier_Matrixdays');
			$this->_ukFiltering = true;
			$longPostcode=substr_replace($postcode,"",-3);
			$this->_longUKPostcode = trim($longPostcode);
			$this->_shortUKPostcode = preg_replace('/\d/','', $this->_longUKPostcode);
		}  else if ($postcodeFilter == 'both') {
            //zend_debug::dump('CASE3','Webshopapps_Matrixdays_Model_Mysql4_Carrier_Matrixdays');
			if(ctype_digit(substr($postcode, 0,1))){
				$this->_zipSearchString = $read->quoteInto(" AND dest_zip<=? ", $postcode).$read->quoteInto(" AND dest_zip_to>=? )", $postcode);
			} else {
				$this->_ukFiltering = true;
				$longPostcode=substr_replace($postcode,"",-3);
				$this->_longUKPostcode = trim($longPostcode);
				$this->_shortUKPostcode = preg_replace('/\d/','', $this->_longUKPostcode);
			}
		} else {
            //zend_debug::dump('CASE4','Webshopapps_Matrixdays_Model_Mysql4_Carrier_Matrixdays');
			 $this->_zipSearchString = $read->quoteInto(" AND ? LIKE dest_zip )", $postcode);
		}
        // if POBOX search on CITY field
        $searchPOBox=false;
    	if (preg_match("/p\.* *o\.* *box/i", $request->getDestStreet())) {
  			$searchPOBox=true;
    		if ($this->_debug) {
  				Mage::helper('wsacommon/log')->postNotice('matrixdays','POBox check','We cannot deliver to PO boxes.');

    		}
		}

        // make global as used all around
        $this->_request=$request;
        $this->_starIncludeAll=Mage::getStoreConfig("carriers/matrixdays/star_include_all");


		$items = $request->getAllItems();
		if (!empty($items) && ($items!="")) {
            //zend_debug::dump($this->_customerGroupCode,'HAVE ITEM');
			$this->_customerGroupCode = Mage::getModel('customer/group')->load
    			($items[0]->getQuote()->getCustomerGroupId())->getCode();
		} else {
			return array();
		}

		// get the package_id's for the items in the cart

		$conditionName=$this->_request->getConditionName();
        //zend_debug::dump($conditionName,'$conditionName');

		$this->populateStructuredItems($items,$conditionName);
    	if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvbWF0cml4ZGF5cy9zaGlwX29uY2U=',
			'Y2FmZWxhdHRl','Y2FycmllcnMvbWF0cml4ZGF5cy9zZXJpYWw=')) {
            //zend_debug::dump('STRANGE CASE','Webshopapps_Matrixdays_Model_Mysql4_Carrier_Matrixdays');

            return array();
		}

        $dayCount=0;
        $dispatchDate='';
		$dispatchDay=Mage::helper('matrixdays')->getDispatchDay($dayCount,$dispatchDate);
        if ($this->_debug) {
            Mage::helper('wsalogger/log')->postDebug('matrixdays','Earliest Dispatch Day',$dispatchDay);
            Mage::helper('wsalogger/log')->postDebug('matrixdays','Earliest Dispatch Date',$dispatchDate);
        }

		$first=true;
		foreach ($this->_structuredItems as $structuredItem) {

            //zend_debug::dump('FOREACH LOOP RUN');
            //zend_debug::dump($structuredItem);

			if ($structuredItem['package_id']=='none' && $this->_starIncludeAll) { continue; }
			$this->_minusOne=false;
			if(!$first) {
				$data=$this->runSelectStmt($read,$structuredItem,$searchPOBox,$dispatchDay,$dayCount,$dispatchDate);
                //zend_debug::dump($data,'CASE 1');
				if (!empty($data)) {
					if ($conditionName=='highest') {
						$this->mergeHighest($data,$finalResults);
					} else if ($conditionName=='lowest') {
						$this->mergeLowest($data,$finalResults);
					} else if ($conditionName=='order') {
						$this->mergeOrdered($data,$finalResults);
					} else {
						$this->mergeResults($data,$finalResults);
					}
				} else if (!$this->_starIncludeAll  || (!$this->_starIncludeAll && $this->_minusOne)){
					return array();
				}
			} else {
				$data=$this->runSelectStmt($read,$structuredItem,$searchPOBox,$dispatchDay,$dayCount,$dispatchDate);
                //zend_debug::dump($data,'CASE 2');
				if (!empty($data)) {
					$first=false;
					$finalResults=$data;
				} else if (!$this->_starIncludeAll  || (!$this->_starIncludeAll && $this->_minusOne)) {
                    //zend_debug::dump('CASE 3');
					return array();
				}
			}
		}


        //zend_debug::dump($finalResults,'FINAL');

		if (empty($finalResults)) { return array(); }
		if (!empty($this->_exclusionList)) {
			foreach ($finalResults as $key=>$result) {
				foreach ($this->_exclusionList as $exclusionItem) {
					if ($result['delivery_type']==$exclusionItem['delivery_type']) {
						$finalResults[$key]="";
						break;
						}
				}

			}
		    foreach ($finalResults as $key=>$result) {
	    		if (empty($finalResults[$key])) {
	    			unset($finalResults[$key]);
	    		}
	    	}
		}

		if (empty($finalResults)) { return array(); }

    	if ($this->_prioritySet) {
		    foreach ($finalResults as $i => $rate) {
		        $priceArr[$i] = $rate['price'];
		        $priority[$i] = $rate['priority'];
		    }

			array_multisort($priceArr, SORT_ASC, $priority, SORT_ASC, $finalResults);
			$previousPrice=-100;
			$previousPriority="";
			foreach ($finalResults as $data) {
				if ($previousPrice==$data['price'] && $data['priority']!=$previousPriority  && is_numeric($data['priority']) && is_numeric($previousPriority)) {
					continue;
				} else {
					$previousPrice=$data['price'];
					$previousPriority=$data['priority'];
					$absoluteResults[]=$data;
				}
			}
		} else {
			$absoluteResults=$finalResults;
		}
		if ($conditionName=="highest" && !$this->_ignoreAdditionalItemPrice)
		{
			foreach ($absoluteResults as $key=>$data) {
				$absoluteResults[$key]['price'] = $data['price'] + $data['additional_price'];
				if ($data['qty']>1 && $data['multiprice']>0 && !$data['override']) {
					$absoluteResults[$key]['price'] = $absoluteResults[$key]['price'] + $data['multiprice']*($data['qty']-1);
				}
				$tempAlgorithm=explode("=",$data['algorithm'],2);
				if (strtolower($tempAlgorithm[0]) == ('ai'))
				{
					if ($data['qty']=1 && $data['multiprice']>0 && !$data['override']) {
						$absoluteResults[$key]['price'] = $absoluteResults[$key]['price'] + $data['multiprice']*($data['qty']);
					}
				}
			}
		}

		if ($this->_maxPriceSet) {
			foreach ($absoluteResults as $key=>$data) {
				if ($data['price']>$data['max_price']) {
					$absoluteResults[$key]['price'] = $data['max_price'];
				}
			}
		}
		$absoluteResults = $this->getCalculatedResults($absoluteResults,$dispatchDate,$dayCount,$dispatchDay);

		Mage::helper('wsacommon/log')->postMinor('matrixdays','Absolute Results',$absoluteResults,$this->_debug);

		if (Mage::helper('matrixdays')->getUsingGrid()) {
			return $this->createDataGrid($absoluteResults,$earliestExpectedDelivery);
		} else {
			return $absoluteResults;
		}

    }

    private function getCalculatedResults($absoluteResults,$dispatchDate,$dayCount,$dispatchDay) {
    	// get delivery day
    	$resultSet = array();
		foreach ($absoluteResults as $key=>$result) {
			$deliveryDay = -1;
			// TODO: Change so works when num_production_days and cut_off_time are both present
			if ($result['num_production_days']!=-1) {
				$revisedDayCount = 0;
				$revisedDispatchDay = '';
				$dispatchDay=Mage::helper('matrixdays')->getDispatchDay($revisedDayCount,$revisedDispatchDay,$result['num_production_days']);
				$result['expected_delivery']=Mage::helper('matrixdays')->getWorkingDate($revisedDispatchDay,
				    $revisedDayCount,$result['num_delivery_days'],$deliveryDay);
				$result['dispatch']=$dispatchDay;
			} else if ($result['cut_off_time']!=-1) {
    			$revisedDayCount = 0;
    			$revisedDispatchDay = '';
    			$dispatchDay=Mage::helper('matrixdays')->getDispatchDay($revisedDayCount,$revisedDispatchDay,0,$this->_earliestCutoff);
    			$result['expected_delivery']=Mage::helper('matrixdays')->getWorkingDate($revisedDispatchDay,
    			   $revisedDayCount,$result['num_delivery_days'],$deliveryDay);
    			$result['dispatch']=$dispatchDay;
			} else {
				$result['expected_delivery']=Mage::helper('matrixdays')->getWorkingDate($dispatchDay,
				    $dayCount,$result['num_delivery_days'],$deliveryDay);
				$absoluteResults[$key]['dispatch']=$dispatchDate;
			}
			$result['delivery_day']=$deliveryDay;

			//TODO Check what this logic is doing!
			if (!Mage::helper('matrixdays')->getUsingGrid()) {
				if (!in_array($deliveryDay,$result['excl']) &&
					(count($result['incl'])<1 || in_array($deliveryDay,$result['incl']))) {
						$resultSet[] = $result;
				}
			} else {
				$resultSet[] = $result;
			}
		}
		return $resultSet;

    }

    // convert to Julian - 2038 bug!!!!!!!!!!!!!!
    private function getLaterDeliveryDay($first,$second) {
    	if ($first['expected_delivery']!='' && $second['expected_delivery']!='') {
		    $date1 = strtotime($first['expected_delivery']);
    		$date2 = strtotime($second['expected_delivery']);

		    // Which is the latest?
		    if ($date2 > $date1) {
		      	$first['expected_delivery']=$second['expected_delivery'];
    			$first['delivery_day']=$second['delivery_day'];
    			$first['append']=$second['append'];
		    }
    	}
    	return $first;
    }

    private function mergeHighest($indResults,&$baseResults)
    {
    	foreach ($baseResults as $key=>$result)
    	{
    	   	$found=false;
    		foreach ($indResults as $indKey=>$data) {
    			if ($result['delivery_type']==$data['delivery_type']) {
    				if (!$baseResults[$key]['override'] && ($data['price']>$baseResults[$key]['price'] || $data['override'])) { // if higher get higher
						$baseResults[$key]['price']=$data['price'];
						$baseResults[$key]['additional_price']+=$baseResults[$key]['multiprice']*$baseResults[$key]['qty'];
						$baseResults[$key]['multiprice']=$data['multiprice'];
						$baseResults[$key]['num_delivery_days']=$data['num_delivery_days'];
						$baseResults[$key]['num_production_days']=$data['num_production_days'];
						$baseResults[$key]['qty']=$data['qty'];
					} else {
						$baseResults[$key]['additional_price']+=$data['multiprice']*$data['qty'];
					}
					$baseResults[$key] = $this->getLaterDeliveryDay($baseResults[$key],$data);
					if ($baseResults[$key]['max_price']<$data['max_price']) {
						$baseResults[$key]['max_price']=$data['max_price'];
					}
					$indResults[$indKey]['found']=true;
					$found=true;
					break;
				}
    		}
    		if (!$found && !$this->_starIncludeAll && !$baseResults[$key]['showall']) {
    		    // no match so remove
    			if ($this->_debug) {
					Mage::helper('wsacommon/log')->postMinor('matrixdays','Delivery Type - No Match Found - Removing',$baseResults[$key]['delivery_type']);
				}
    			$baseResults[$key]="";
    		}
    	}
    	// get show all items
    	foreach ($indResults as $key=>$result)
    	{
    		if ( !$found && $result['showall']) {
    			$baseResults[]=$result;
    			$indResults[$key]['found']=true;
    		}
    	}

    	if ($this->_starIncludeAll) {
    	   	// check for missing
	    	foreach ($indResults as $data) {
	    		if (empty($data['found'])) {
	    			$baseResults[]=$data;
	    		}
	    	}
    	} else {
	     	// unset here so we dont upset the apple cart
	    	foreach ($baseResults as $key=>$result) {
	    		if (empty($baseResults[$key])) {
	    			unset($baseResults[$key]);
	    		}
	    	}
    	}
    }




    /**
     * Merge results together, ignore any not in base result set
     * @param $indResults
     * @param $baseResults - array passed by reference
     */
    private function mergeResults($indResults,&$baseResults)
    {
    	if ($this->_debug) {
    		Mage::helper('wsacommon/log')->postMinor('matrixdays','Individual Results',$indResults);
    		Mage::helper('wsacommon/log')->postMinor('matrixdays','Base Results',$baseResults);
    	}
    	foreach ($baseResults as $key=>$result)
    	{
    		$found=false;
    		foreach ($indResults as $indKey=>$data) {
    			if ($result['delivery_type']==$data['delivery_type']) {
					$baseResults[$key]['found']=true;
					$indResults[$indKey]['found']=true;
					$baseResults[$key] = $this->getLaterDeliveryDay($baseResults[$key],$data);
					if ($baseResults[$key]['max_price']<$data['max_price']) {
						$baseResults[$key]['max_price']=$data['max_price'];
					}
    				if ($baseResults[$key]['num_delivery_days']<$data['num_delivery_days']) {
						$baseResults[$key]['num_delivery_days']=$data['num_delivery_days'];
					}
    			  	if ($baseResults[$key]['num_production_days']<$data['num_production_days']) {
						$baseResults[$key]['num_production_days']=$data['num_production_days'];
					}

    				if ($baseResults[$key]['override']) {
    					$found=true;
    					break;
    				} else if ($data['override']) {
    					$baseResults[$key]['price']=$data['price'];
    					$baseResults[$key]['override']=true;
    					$baseResults[$key]['package_id']=$baseResults[$key]['package_id'].",".$data['package_id'];
    					$found=true;
						break;
    				} else {
						// add to existing
						$baseResults[$key]['price']+=$data['price'];
						$baseResults[$key]['package_id']=$baseResults[$key]['package_id'].",".$data['package_id'];
						$found=true;
						break;
    				}
				}
    		}
    		if (!$found && !$baseResults[$key]['showall']) {  // no match
    			if ( !$this->_starIncludeAll) {
    				if ($this->_debug) {
						Mage::helper('wsacommon/log')->postMinor('matrixdays','Delivery Type - No Match Found - Removing',$baseResults[$key]['delivery_type']);
					}
    				$baseResults[$key]="";
    			} else {
    				if ($result['package_id']!="" && count($this->_structuredItems)>1 && $indResults[0]['package_id']!="" ) {
    					if ($this->_debug) {
							Mage::helper('wsacommon/log')->postMinor('matrixdays','Delivery Type - No Match Found - Removing',$baseResults[$key]['delivery_type']);
						}
    					$baseResults[$key]="";
    				}
    			}
    		}
    	}
    	if ($this->_starIncludeAll) {
    	   	// check for missing
	    	foreach ($indResults as $data) {
	    		if (empty($data['found']) && $data['package_id']=="") {
	    			$baseResults[]=$data;
	    		}
	    	}

	    	// this was changed to be ==package_id from != - reason unclear
	    	if ($this->_appendStarRates) {
		    	foreach ($baseResults as $key=>$result) {
		    		if ($result!="" && $this->_hasEmptyPackages && $result['package_id']=="") {
		    			if ($this->_debug) {
							Mage::helper('wsacommon/log')->postMinor('matrixdays','Delivery Type - No Match Found - Removing',$baseResults[$key]['delivery_type']);
						}
	    				$baseResults[$key]="";
		    		}
		    	}
	    	} else {
		    	foreach ($baseResults as $key=>$result) {
		    		if ($result!=""  && !$baseResults[$key]['showall'] && (!isset($result['found']) || !$result['found'])) {
		    			if ($this->_debug) {
							Mage::helper('wsacommon/log')->postMinor('matrixdays','Delivery Type - No Match Found - Removing',$baseResults[$key]['delivery_type']);
						}
	    				$baseResults[$key]="";
		    		}
		    	}
	    	}
    	}
    	// unset here so we dont upset the apple cart
    	foreach ($baseResults as $key=>$result) {
    		if (empty($baseResults[$key])) {
    			unset($baseResults[$key]);
    		}
    	}

    }

    /**
     * Doesnt support delivery day
     * @param $indResults
     * @param $baseResults
     */
    private function mergeLowest($indResults,&$baseResults)
    {

    	foreach ($baseResults as $key=>$result)
    	{
    	   	$found=false;
    		foreach ($indResults as $indKey=>$data) {
    			if ($result['delivery_type']==$data['delivery_type']) {
					// if lower get lower
					if ($data['price']<$baseResults[$key]['price']) {
						$baseResults[$key]['price']=$data['price'];
					}
					$indResults[$indKey]['found']=true;
					$found=true;
					break;
				}
    		}
    		if (!$found && !$this->_starIncludeAll) {
    			// no match so remove
    			$baseResults[$key]="";
    		}
    	}

    	if ($this->_starIncludeAll) {
    	   	// check for missing
	    	foreach ($indResults as $data) {
	    		if (empty($data['found'])) {
	    			$baseResults[]=$data;
	    		}
	    	}
    	} else {
	    	// unset here so we dont upset the apple cart
	    	foreach ($baseResults as $key=>$result) {
	    		if (empty($baseResults[$key])) {
	    			unset($baseResults[$key]);
	    		}
	    	}
    	}
    }

    // $dayCount is not being used currently
	private function runSelectStmt($read,$structuredItem,$searchPOBox,$dispatchDay,$dayCount,$dispatchDate)
	{
        //zend_debug::dump('runSelectStmt','Webshopapps_Matrixdays_Model_Mysql4_Carrier_Matrixdays');

		$conditionName=$this->_request->getConditionName();

        //zend_debug::dump($conditionName,'Webshopapps_Matrixdays_Model_Mysql4_Carrier_Matrixdays $conditionName');

		if ($this->_postcodeFiltering == 'uk') {
			$switchSearches=13;
		} else {
			$switchSearches=9;
		}

        //zend_debug::dump($switchSearches,'Webshopapps_Matrixdays_Model_Mysql4_Carrier_Matrixdays $switchSearches');


        for ($j=0;$j<$switchSearches;$j++)
		{
			$select = $this->getSwitchSelect($read,$j,$searchPOBox);

			$addOr = FALSE;

			if ($structuredItem['package_id']=='include_all' || $structuredItem['package_id']=='none') {
				$select->where('package_id=?','');
			} else {
				$select->where('package_id=?', $structuredItem['package_id']);
			}

			if($this->_volumeSwitch) {
    			$totalVolweight=$structuredItem['volume'];

    			if ($this->_usingGreaterVolLogic) {
    			    if( $structuredItem['volume'] > $structuredItem['weight'] ) {
    			        $this->_greaterVolume = true;
    			        $select->where('weight_from_value<?', $totalVolweight);
    			        $select->where('weight_to_value>=?', $totalVolweight);

    			        if (!empty($this->_alwaysUseWeight)){
    			            foreach (explode(",",$this->_alwaysUseWeight) as $method) {
    			                $select->where('delivery_type<>?', $method);
    			            }
    			            $addOr = TRUE;
    			        }
    			    } else {
    			        $select->where('weight_from_value<?', $structuredItem['weight']);
    			        $select->where('weight_to_value>=?', $structuredItem['weight']);
    			    }
    			} else {
        			$select->where('weight_from_value<?', $structuredItem['weight']);
        			$select->where('weight_to_value>=?', $structuredItem['weight']);
        			$select->where('volume_from_value<?', $structuredItem['volume']);
        			$select->where('volume_to_value>=?', $structuredItem['volume']);
    			}
		    }

		    if(!$this->_volumeSwitch) {
		        $select->where('weight_from_value<?', $structuredItem['weight']);
		        $select->where('weight_to_value>=?', $structuredItem['weight']);
		    }

			$select->where('price_from_value<?', $structuredItem['price']);
			$select->where('price_to_value>=?', $structuredItem['price']);
			$select->where('item_from_value<?', $structuredItem['qty']);
			$select->where('item_to_value>=?', $structuredItem['qty']);

			$groupArr[0]="STRCMP(LOWER(customer_group),LOWER('".$this->_customerGroupCode."')) =0";
			$groupArr[1]="customer_group=''";
			$select->where(join(' OR ', $groupArr));

			$select->where('website_id=?', $this->_request->getWebsiteId());
			$select->where("day=? OR day=''", $dispatchDay);

			$select->order('notes ASC');
			$select->order('price ASC');
			$select->order('algorithm ASC');

            //zend_debug::dump($select->__toString(),'Webshopapps_Matrixdays_Model_Mysql4_Carrier_Matrixdays getSwitchSelect');

			if($addOr) {
                //zend_debug::dump('NEW SELECT');
			    //TODO is there a way to use the existing $select without loosing the first line of the where clause (destination info)

			    $newSelect = $this->getSwitchSelect($read,$j,$searchPOBox);

			    if ($structuredItem['package_id']=='include_all' || $structuredItem['package_id']=='none') {
			        $newSelect->where('package_id=?','');
			    } else {
			        $newSelect->where('package_id=?', $structuredItem['package_id']);
			    }
			    $newSelect->where('weight_from_value<?', $structuredItem['weight']);
			    $newSelect->where('weight_to_value>=?', $structuredItem['weight']);

			    foreach (explode(",",$this->_alwaysUseWeight) as $method) {
			        $newSelect->where('delivery_type=?', $method);
			    }

			    $newSelect->where('price_from_value<?', $structuredItem['price']);
			    $newSelect->where('price_to_value>=?', $structuredItem['price']);
			    $newSelect->where('item_from_value<?', $structuredItem['qty']);
			    $newSelect->where('item_to_value>=?', $structuredItem['qty']);

			    $groupArr[0]="STRCMP(LOWER(customer_group),LOWER('".$this->_customerGroupCode."')) =0";
			    $groupArr[1]="customer_group=''";
			    $newSelect->where(join(' OR ', $groupArr));

			    $newSelect->where('website_id=?', $this->_request->getWebsiteId());
			    $newSelect->where("day=? OR day=''", $dispatchDay);

			    $newSelect->order('notes ASC');
			    $newSelect->order('price ASC');
			    $newSelect->order('algorithm ASC');
			}

			/*
			pdo has an issue. we cannot use bind
			*/
			try {
				$row = $read->fetchAll($select);
				if($addOr) {
				    $row2 = $read->fetchAll($newSelect);
				}
			} catch (Exception $e) {
				 	Mage::helper('wsacommon/log')->postMajor('matrixdays','SQL Exception',$e);
			}

            //zend_debug::dump($row,'Webshopapps_Matrixdays_Model_Mysql4_Carrier_Matrixdays RESULT');

			if($addOr) {
			    $row = array_merge($row,$row2);
			}

			if (!empty($row)) {
				if ($this->_debug) {
					Mage::helper('wsacommon/log')->postNotice('matrixdays','SQL Select',$select->getPart('where'));
					Mage::helper('wsacommon/log')->postNotice('matrixdays','SQL Result',$row);
				}
				$newdata=array();
				$priorityData=array();
				foreach ($row as $data) {
					if ($data['price']==-1) {

						$exclusionItem=array ( 'package_id' => $structuredItem['package_id'],
											   'delivery_type' => $data['delivery_type']);
						$this->_exclusionList[]=$exclusionItem;
						$this->_minusOne=true;
						continue;
					}
					$data['priority']=0;
					$data['multiprice']="";
					$data['additional_price']=0;
					$data['qty']=0;
					$data['max_price']=-1;
					$data['same_day']=false;
					$data['override']=false;
					$data['showall']=false;
					$data['num_delivery_days']=1;
					$data['num_production_days'] = -1;
					$data['cut_off_time'] = -1;
					$decrease = -1;
					$data['excl']=array();
					$data['incl']=array();
					$data['slot']=array();
					$data['append']="";
					$data['method_name']=$data['delivery_type'];
					$priorityTypes=array();
					if ($data['algorithm']!="") {
						$algorithm_array=explode("&",$data['algorithm']);  // Multi-formula extension
						reset($algorithm_array);
						$skipData=false;
						foreach ($algorithm_array as $algorithm_single) {
							$algorithm=explode("=",$algorithm_single,2);
							if (!empty($algorithm) && count($algorithm)==2) {
								if (strtolower($algorithm[0])=="w") {
									// weight based
									$weightIncrease=explode("@",$algorithm[1]);
									if (!empty($weightIncrease) && count($weightIncrease)==2 ) {
									    if ($this->_greaterVolume && $this->_volumeSwitch) {
											$weightDifference =	$structuredItem['volume']-$data['volume_from_value'];
										} else {
											$weightDifference=	$structuredItem['weight']-$data['weight_from_value'];
										}
										$quotient=$weightDifference / $weightIncrease[0];
										$data['price']=$data['price']+$weightIncrease[1]*$quotient;
									}
								}else if (strtolower($algorithm[0])=="wa") {
									$weightIncrease=explode("@",$algorithm[1]);
								if (!empty($weightIncrease) && count($weightIncrease)==2 ) {
										$weight= $structuredItem['weight'];
										$quotient=$weight / $weightIncrease[0];
										$data['price']=$data['price']+$weightIncrease[1]*$quotient;
									}
								} else if (strtolower($algorithm[0])=="wc") {
									// weight based
									$weightIncrease=explode("@",$algorithm[1]);
									if (!empty($weightIncrease) && count($weightIncrease)==2 ) {
										$weightDifference=	$structuredItem['weight']-$data['weight_from_value'];
										$quotient=ceil($weightDifference / $weightIncrease[0]);
										$data['price']=$data['price']+$weightIncrease[1]*$quotient;
									}
								}else if (strtolower($algorithm[0])=="wca") {
									// weight based
									$weightIncrease=explode("@",$algorithm[1]);
									if (!empty($weightIncrease) && count($weightIncrease)==2 ) {
										$weight= $structuredItem['weight'];
										$quotient=ceil($weight / $weightIncrease[0]);
										$data['price']=$data['price']+$weightIncrease[1]*$quotient;
									}
								}else if (strtolower($algorithm[0])=="p" ) {
									$this->_prioritySet=true;
									$data['priority']=$algorithm[1];
								} else if (strtolower($algorithm[0])=="alt" ) {
									$priorityTypes=explode(",",$algorithm[1]);
                                } else if (strtolower($algorithm[0])=="sameday") {
                                    $data['same_day']= true;
								} else if (strtolower($algorithm[0])=="bd") {
									Mage::helper('matrixdays')->addBlackoutDeliveryDay($algorithm[1]);
								} else if (strtolower($algorithm[0])=="m" ) {
									$this->_maxPriceSet = true;
									$data['max_price']=$algorithm[1];
								} else if (strtolower($algorithm[0])=="i" ) {
									if (strtolower($algorithm[1])=='ignore') {
										$this->_ignoreAdditionalItemPrice=true;
									} else {
										if ($conditionName=='per_package') {
											$data['price']+=$algorithm[1]*($structuredItem['qty']-$data['item_from_value']);
										} else {
											$data['multiprice']=$algorithm[1];
											$data['qty']=$structuredItem['qty'];
										}
									}
								} else if (strtolower($algorithm[0])=="io" ) {
									if ($conditionName=='per_package') {
										$data['price']+=$algorithm[1]*($structuredItem['qty']-$data['item_from_value']);
									} else {
										$data['multiprice']=$algorithm[1];
										$data['qty']=1;
									}
								} else if (strtolower($algorithm[0])=="im") {
									$itemIncrease=explode("@",$algorithm[1]);
									if (!empty($itemIncrease) && count($itemIncrease)==2 ) {
										$qty=$structuredItem['qty'];
										$quotient=ceil($qty / $itemIncrease[0]);
										if ($conditionName=='per_package') {
											$data['price']=$data['price']+$itemIncrease[1]*$quotient;
										} else {
											$data['multiprice']=$data['price']+$itemIncrease[1]*$quotient;
											$data['qty']=$structuredItem['qty'];
										}
									}
								} else if (strtolower($algorithm[0])=="ai" ) {
									if ($conditionName=='per_package') {
										$data['price']+=$algorithm[1]*($structuredItem['qty']);
									} else {
										$data['multiprice']=$algorithm[1];
										$data['qty']=$structuredItem['qty'];
									}
								} else if (strtolower($algorithm[0])=="a" ) {
									if ($this->_request->getUpsDestType()=='RES') {
										if ($algorithm[1]!='residential') {
											$skipData = true;
											break;
										}
									} else {
										if ($algorithm[1]!='commercial') {
											$skipData = true;
											break;
										}
									}
								} else if (strtolower($algorithm[0])=="o" ) {
									$data['order']=$algorithm[1];
								} else if (strtolower($algorithm[0])=="%" ) {
									$perSplit=explode("+",$algorithm[1]);
									if (!empty($perSplit) && count($perSplit)==2) {
										$percentage = $perSplit[0];
										$flatAdd = $perSplit[1];
									} else {
										$percentage = $algorithm[1];
										$flatAdd = 0;
									}
									$percPrice=($structuredItem['price']*$percentage/100)+$flatAdd;
									if ($percPrice>$data['price']) {
										$data['price']=$percPrice;
									}
								} else if (strtolower($algorithm[0])=="r") {
									$decrease=$algorithm[1];
								} else if (strtolower($algorithm[0])=="c") {
									$data['method_name']=$algorithm[1];
								} else if (strtolower($algorithm[0])=="instock" && strtolower($algorithm[1])=="true") {
									if (!$this->_stockFound) {
										if ($this->checkOutOfStock($this->_request)) {
											$skipData = true;
											break;
										}
									} else {
										if ($this->_outofstock) {
											$skipData = true;
											break;
										}
									}
								}	else if (strtolower($algorithm[0])=="instock" && strtolower($algorithm[1])=="false") {
									if (!$this->_stockFound) {
										if (!$this->checkOutOfStock($this->_request)) {
											$skipData = true;
											break;
										}
									} else {
										if (!$this->_outofstock) {
											$skipData = true;
											break;
										}
									}
								} else if (strtolower($algorithm[0])=="d" || strtolower($algorithm[0])=="del_days" ) {
									$data['num_delivery_days']=$algorithm[1];
								} else if (strtolower($algorithm[0])=="prod_days") {
									$data['num_production_days'] = $algorithm[1];
								} else if (strtolower($algorithm[0])=="cut_off") {
									if(strtotime($this->_earliestCutoff) > strtotime($algorithm[1])){
										$this->_earliestCutoff=$algorithm[1];
										$data['cut_off_time'] = $algorithm[1];
									} else $data['cut_off_time'] =  $this->_earliestCutoff;
								} else if (strtolower($algorithm[0])=="incl") {
									$data['incl'] = explode(',',$algorithm[1]);
								} else if (strtolower($algorithm[0])=="excl" ) {
									$data['excl']=explode (',',$algorithm[1]);
								} else if (strtolower($algorithm[0])=="append" && $algorithm[1]!='' ) {
									$data['append']=$algorithm[1];
								} else if (strtolower($algorithm[0])=="slot" && $algorithm[1]!='' ) {
									$data['slot']=explode(',',$algorithm[1]);
								}
							} else {
								switch ($algorithm_single) {
									case "OVERRIDE":
										$data['override']=true;
										break;
									case "OVERRIDE_SINGLE":
										if (count($this->_structuredItems)<3) {
											$data['override']=true;
										}
										break;
									case "SHOWALL":
										$data['showall']=true;
										break;
								}
							}

						}
						if ($skipData) { continue; }
						if ($decrease>-1) {
							$data['price'] = $data['price']-$decrease;
							if ($data['price']<0) { $data['price']=0; }
						}
					}

					if ($conditionName=='per_product') {
						// for each unique product in basket for this package id (e.g. product A&B of package id Z)
						$data['price']=$data['price']*$structuredItem['unique'];
					} else if ($conditionName=='per_item' || $conditionName=='per_item_bare') {
						// foreach item in basket for this package_id (e.g. 3*product A of package id Z)
						$data['price']=$data['price']*$structuredItem['qty'];
					}else if ($conditionName=='per_product_bare') {
						$data['price']=$data['price']*$structuredItem['unique'];
					}
					else if ($structuredItem['package_id']!='include_all' && $conditionName=='per_item_surcharge'  ) {
						$data['price']=$data['price']*$structuredItem['qty'];
					}
					$data['dispatch']=$dispatchDate;
					$deliveryDay = -1;
					$data['num_production_days'] != -1 ? $pDays=$data['num_production_days'] : $pDays=0;
					$data['expected_delivery'] = Mage::helper('matrixdays')->getWorkingDate($dispatchDay,$dayCount,$pDays,$deliveryDay);
					$data['delivery_day']=$deliveryDay;

                    if (count($priorityTypes) > 0) {
						$data['priority']=0;
						$priorityNum=1;
						$this->_prioritySet=true;
						foreach ($priorityTypes as $priorityType) {
							$copyData = $data;
							$copyData['priority']=$priorityNum;
							$priorityNum++;
							$copyData['delivery_type']=$priorityType;
							$copyData['method_name']=$priorityType;
							$priorityData[]=$copyData;
						}

					}

					$newdata[]=$data;
				}
				if (count($priorityData)>0) {
					$newdata = array_merge($newdata, $priorityData);
				}
				if (!empty($newdata)) {
    				return $newdata;
				} else {
					return array();
				}
			}
		}
	}

	private function checkOutOfStock($request) {
		$items = $request->getAllItems();
    	foreach($items as $item) {
    		if ($item->getBackorders() != Mage_CatalogInventory_Model_Stock::BACKORDERS_NO) {
    			$this->_outofstock=true;
    		}
    	}
    	$this->_stockFound=true;
    	return $this->_outofstock;
    }

	private function getSwitchSelect($read,$j,$searchPOBox) {

		if ($searchPOBox) {
			$destCity='POBOX';
		} else {
			$destCity=$this->_request->getDestCity();
		}


		//$select = $read->select()->from($table);
		$select = $read->select()->from(array('matrixdays'=>$this->_table),
						array(	'pk'=>'pk',
								'price'=>'price',
								'delivery_type'=>'delivery_type',
								'package_id'=>'package_id',
								'weight_from_value'=>'weight_from_value',
								'item_from_value'=>'item_from_value',
								'algorithm'=>'algorithm',
								'notes'=>'notes',
								'cost'=>'cost'));

		if ($this->_ukFiltering) {
			switch($j) {
				case 0:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? ", $this->_request->getDestRegionId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  ", $destCity).
							$read->quoteInto(" AND STRCMP(LOWER(dest_zip),LOWER(?)) = 0)", $this->_longUKPostcode)
						);
					break;
				case 1:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? ", $this->_request->getDestRegionId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  ", $destCity).
							$read->quoteInto(" AND STRCMP(LOWER(dest_zip),LOWER(?)) = 0 )", $this->_shortUKPostcode)
					);
					break;
				case 2:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=?  AND dest_city=''", $this->_request->getDestRegionId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_zip),LOWER(?)) = 0 )", $this->_longUKPostcode)
						);
					break;
				case 3:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=?  AND dest_city=''", $this->_request->getDestRegionId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_zip),LOWER(?)) = 0 )", $this->_shortUKPostcode)
						);
					break;
				case 4:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? ", $this->_request->getDestRegionId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_zip='')", $destCity)
						);
					break;
				case 5:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0'", $destCity).
							$read->quoteInto(" AND STRCMP(LOWER(dest_zip),LOWER(?)) = 0 )", $this->_longUKPostcode)
					   );
					break;
				case 6:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0'", $destCity).
							$read->quoteInto(" AND STRCMP(LOWER(dest_zip),LOWER(?)) = 0 )", $this->_shortUKPostcode)
					   );
					break;
				case 7:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0' AND dest_zip='') ", $destCity)
					   );
					break;
				case 8:
					$select->where(
						$read->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_zip),LOWER(?)) = 0 )", $this->_longUKPostcode)
							);
					break;
				case 9:
					$select->where(
						$read->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_zip),LOWER(?)) = 0 )", $this->_shortUKPostcode)
							);
					break;
				case 10:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? AND dest_city='' AND dest_zip='') ", $this->_request->getDestRegionId())
					   );
					break;

				case 11:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' AND dest_zip='') ", $this->_request->getDestCountryId())
					);
					break;

				case 12:
					$select->where(
							"  (dest_country_id='0' AND dest_region_id='0' AND dest_zip='')"
				);
					break;
			}

		} else {
			switch($j) {
				case 0:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? ", $this->_request->getDestRegionId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  ", $destCity).
							$this->_zipSearchString
						);
					break;
				case 1:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=?  AND dest_city=''", $this->_request->getDestRegionId()).
							$this->_zipSearchString
						);
					break;
				case 2:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? ", $this->_request->getDestRegionId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_zip='')", $destCity)
						);
					break;
				case 3:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0'", $destCity).
							$this->_zipSearchString
					   );
					break;
				case 4:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0' AND dest_zip='') ", $destCity)
					   );
					break;
				case 5:
					$select->where(
						$read->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' ", $this->_request->getDestCountryId()).
							$this->_zipSearchString
						);
					break;
				case 6:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? AND dest_city='' AND dest_zip='') ", $this->_request->getDestRegionId())
					   );
					break;

				case 7:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' AND dest_zip='') ", $this->_request->getDestCountryId())
					);
					break;

				case 8:
					$select->where(
							"  (dest_country_id='0' AND dest_region_id='0' AND dest_zip='')"
				);
					break;
			}
		}


		return $select;
	}

	private function populateStructuredItems($items, $conditionName)
	{
		$specialPrice = 0;
		$specialWeight = 0;
		$this->_structuredItems=array();
		$this->_useParent = Mage::getStoreConfig("carriers/matrixdays/parent_group");
		if ($this->_debug) {
			Mage::helper('wsacommon/log')->postNotice('matrixdays','Settings','Use Parent:'.$this->_useParent);
		}
		switch ($this->_useParent) {
			case "none":
				$this->_useBundleParent=false;
				$this->_useConfigurableParent=false;
				break;
			case "both":
				$this->_useBundleParent=true;
				$this->_useConfigurableParent=true;
				break;
			case "bundle":
				$this->_useBundleParent=true;
				$this->_useConfigurableParent=false;
				break;
			case "configurable":
				$this->_useBundleParent=false;
				$this->_useConfigurableParent=true;
				break;
			default:
				$this->_useBundleParent=false;
				$this->_useConfigurableParent=false;
				break;

		}

		$filterPrice = in_array('filter_subtotal',$this->_options);
		$filterWeight = in_array('filter_weight',$this->_options);
		$filterVolume = in_array('filter_volume',$this->_options);
		$useDiscountValue = in_array('use_discounted',$this->_options);
		$volumeMultipler = Mage::getStoreConfig("carriers/matrixdays/volume_multiplier");
		$totalVolumeWeight=0;

		foreach($items as $item) {

			$weight=0;
			$qty=0;
			$price=0;


			if (!Mage::helper('wsacommon/shipping')->getItemTotals($item, $weight,$qty,$price,$this->_useBundleParent,true)) {
				continue;
			}

			if ($item->getParentItem()!=null &&
				$this->_useBundleParent &&   $item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE ) {
					// must be a bundle/configurable
					$product = Mage::getModel('catalog/product')->getResourceCollection()
					    ->addAttributeToSelect('package_id')
					    ->addAttributeToSelect('shipping_qty')
					    ->addAttributeToSelect('ship_height')
					    ->addAttributeToSelect('ship_width')
					    ->addAttributeToSelect('ship_depth')
					    ->addAttributeToSelect('volume_weight')
					    ->addAttributeToFilter('entity_id',$item->getParentItem()->getProductId());
			} else if ($item->getParentItem()!=null &&
				$this->_useConfigurableParent &&   $item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE ) {
					$product = Mage::getModel('catalog/product')->getResourceCollection()
					    ->addAttributeToSelect('package_id')
					    ->addAttributeToSelect('shipping_qty')
					    ->addAttributeToSelect('ship_height')
					    ->addAttributeToSelect('ship_width')
					    ->addAttributeToSelect('ship_depth')
					    ->addAttributeToSelect('volume_weight')
					    ->addAttributeToFilter('entity_id',$item->getParentItem()->getProductId());
			} else if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE && !$this->_useConfigurableParent ) {
				if ($item->getHasChildren()) {
                	foreach ($item->getChildren() as $child) {
                		$product = Mage::getModel('catalog/product')->getResourceCollection()
						    ->addAttributeToSelect('package_id')
						    ->addAttributeToSelect('shipping_qty')
						    ->addAttributeToSelect('ship_height')
						    ->addAttributeToSelect('ship_width')
						    ->addAttributeToSelect('ship_depth')
						    ->addAttributeToSelect('volume_weight')
						    ->addAttributeToFilter('entity_id',$child->getProductId());
						    break;
                	}
				}
			} else {
				$product = Mage::getModel('catalog/product')->getResourceCollection()
					    ->addAttributeToSelect('package_id')
					    ->addAttributeToSelect('shipping_qty')
					    ->addAttributeToSelect('ship_height')
					    ->addAttributeToSelect('ship_width')
					    ->addAttributeToSelect('ship_depth')
					    ->addAttributeToSelect('volume_weight')
					    ->addAttributeToFilter('entity_id',$item->getProductId());
			}


			// if find a surcharge check to see if it is on the item or the order
			// if on the order then add to the surcharge_order_price if > than previous
			// if on the item then multiple by qty and add to the surcharge_price
			foreach($product as $object) {
				continue;
			}

			$shipQty=$object->getData('shipping_qty');

			if ($shipQty=="" || !is_numeric($shipQty)) {
				$shipQty=1;
			}

			$height=$object->getData('ship_height');
			$width=$object->getData('ship_width');
			$depth=$object->getData('ship_depth');
			$definedVolumeWeight=$object->getData('volume_weight');

			if ($height=="" || !is_numeric($height) ||
			        $width=="" || !is_numeric($width) ||
			        $depth=="" || !is_numeric($depth) ||
			        $definedVolumeWeight=="" || !is_numeric($definedVolumeWeight)
			        || !$this->_volumeSwitch) {
			    $volumeWeight=0;
			} else {
			    $volumeWeight = $definedVolumeWeight == "" ? $height*$width*$depth*$volumeMultipler : $definedVolumeWeight;
			}

			$totalVolumeWeight+=$volumeWeight*$qty;

			if (in_array('group_text',$this->_options)) {
				$packageId = $object->getData('package_id');
			} else {
				$packageId = $object->getAttributeText('package_id');
			}
			$found=false;

			if (empty($packageId)) { $packageId='none'; $this->_hasEmptyPackages=true; }

			if ($conditionName=='per_item_bare' || $conditionName=='per_item_surcharge' ||  $conditionName=='per_product_bare') {
				$prodArray=array( 'package_id'  => $packageId,
						  'qty' 		=> $qty,
						  'weight'		=> $weight/$qty,
				          'volume'		=> $volumeWeight,
						  'price'		=> $price/$qty,
						  'unique'		=> 1);
				$this->_structuredItems[]=$prodArray;

			} else {

				foreach($this->_structuredItems as $key=>$structuredItem) {
					if ($structuredItem['package_id']==$packageId) {
						// have already got this package id
						$this->_structuredItems[$key]['qty']=$this->_structuredItems[$key]['qty']+$qty*$shipQty;
						$this->_structuredItems[$key]['weight']=$this->_structuredItems[$key]['weight']+ $weight;
						$this->_structuredItems[$key]['volume']=$this->_structuredItems[$key]['volume']+ $volumeWeight*$qty;
						$this->_structuredItems[$key]['price']=$this->_structuredItems[$key]['price']+ $price;
						$this->_structuredItems[$key]['unique']+=1;
						$found=true;
						break;
					}
				}

				if (!$found){
					$prodArray=array( 'package_id'  => $packageId,
							  'qty' 				=> $qty*$shipQty,
							  'weight'				=> $weight,
					          'volume'				=> $volumeWeight*$qty,
							  'price'				=> $price,
							  'unique'				=> 1);
					$this->_structuredItems[]=$prodArray;

				}
			}
			// also add to include_all package Id
			if ($this->_starIncludeAll ) {
				if ($packageId=="SPECIAL_FREE") {
					$specialPrice += $price;
					$specialWeight += $weight;
				} else {
					$found=false;
					if ($useDiscountValue) {
						$groupPrice = $this->_request->getPackageValueWithDiscount();
					} else {
						$groupPrice = $price;
					}
					foreach($this->_structuredItems as $key=>$structuredItem) {
						if ($structuredItem['package_id']=='include_all') {
							$this->_structuredItems[$key]['qty']=$this->_structuredItems[$key]['qty']+$qty*$shipQty;
							$this->_structuredItems[$key]['weight']=$this->_structuredItems[$key]['weight']+ $weight;
							$this->_structuredItems[$key]['volume']=$this->_structuredItems[$key]['volume']+ $volumeWeight*$qty;
							if (!$useDiscountValue) {
								$this->_structuredItems[$key]['price']=$this->_structuredItems[$key]['price']+ $price;
							}
							$this->_structuredItems[$key]['unique']+=1;
							$found=true;
							break;
						}
					}
					if (!$found) {
						$prodArray=array( 'package_id'  => 'include_all',
						  'qty' 		=> $qty*$shipQty,
						  'weight'		=> $weight,
						  'volume'		=> $volumeWeight*$qty,
						  'price'		=> $groupPrice,
						  'unique'		=> 1);
						$this->_structuredItems[]=$prodArray;
					}
				}
			}
		}
		if ($filterPrice) {
			foreach($this->_structuredItems as $key=>$structuredItem) {
				$filterPrice ? $this->_structuredItems[$key]['price'] = $this->_request->getPackageValue() - $specialPrice : 0;
				$filterWeight ? $this->_structuredItems[$key]['weight'] = $this->_request->getPackageWeight() - $specialWeight : 0;
				$filterVolume ? $this->_structuredItems[$key]['volume']= $totalVolumeWeight : 0;
			}
		}
		if ($this->_debug) {
			Mage::helper('wsacommon/log')->postNotice('matrixdays','Structured Items',$this->_structuredItems);
		}
	}



	/**
	 * CSV Import routine
	 * @param $object
	 * @return unknown_type
	 */
    public function uploadAndImport(Varien_Object $object)
    {
        $csvFile = $_FILES["groups"]["tmp_name"]["matrixdays"]["fields"]["import"]["value"];
        $dataStored = false;
		$session = Mage::getSingleton('adminhtml/session');
		$volumeSwitch = Mage::getConfig()->getNode('matrixdays/specialvars/volume') == 1;;

        if (!empty($csvFile)) {

            $csv = trim(file_get_contents($csvFile));

            $table = Mage::getSingleton('core/resource')->getTableName('matrixdays_shipping/matrixdays');

            $websiteId = $object->getScopeId();

            if (!empty($csv)) {
                $exceptions = array();
                $csvLines = explode("\n", $csv);
                $csvLine = array_shift($csvLines);
                $csvLine = $this->_getCsvValues($csvLine);
                if (count($csvLine) < 18) {
                    $exceptions[0] = Mage::helper('shipping')->__('Invalid Matrix Days File Format');
                }

                $countryCodes = array();
                $regionCodes = array();
                foreach ($csvLines as $k=>$csvLine) {
                    $csvLine = $this->_getCsvValues($csvLine);
                    if (count($csvLine) > 0 && count($csvLine) < 18) {
                        $exceptions[0] = Mage::helper('shipping')->__('Invalid Matrix Days File Format %s',$csvLine);
                    } else {
                        $splitCountries = explode(",", trim($csvLine[0]));
                    	$splitRegions = explode(",", trim($csvLine[1]));
                        foreach ($splitCountries as $country) {
                        	$countryCodes[] = trim($country);
                    	}
                    	foreach ($splitRegions as $region) {
                        	$regionCodes[] = $region;
                    	}
                   	}
                }

             	if (empty($exceptions)) {
                    $connection = $this->_getWriteAdapter();

                     $condition = array(
                        $connection->quoteInto('website_id = ?', $websiteId),
                    );
                    $connection->delete($table, $condition);



                }
                if (!empty($exceptions)) {
                    throw new Exception( "\n" . implode("\n", $exceptions) );
                }


                if (empty($exceptions)) {
                	$data = array();
                    $countryCodesToIds = array();
                    $regionCodesToIds = array();
                    $countryCodesIso2 = array();
                    $counter=0;

                    $countryCollection = Mage::getResourceModel('directory/country_collection')->addCountryCodeFilter($countryCodes)->load();
                    foreach ($countryCollection->getItems() as $country) {
                        $countryCodesToIds[$country->getData('iso3_code')] = $country->getData('country_id');
                        $countryCodesToIds[$country->getData('iso2_code')] = $country->getData('country_id');
                        $countryCodesIso2[] = $country->getData('iso2_code');
                    }

                    $regionCollection = Mage::getResourceModel('directory/region_collection')
                        ->addRegionCodeFilter($regionCodes)
                        ->addCountryFilter($countryCodesIso2)
                        ->load();


                    foreach ($regionCollection->getItems() as $region) {
                        $regionCodesToIds[$countryCodesToIds[$region->getData('country_id')]][$region->getData('code')] = $region->getData('region_id');
                    }

                    foreach ($csvLines as $k=>$csvLine) {
                        $csvLine = $this->_getCsvValues($csvLine);
                        $splitCountries = explode(",", trim($csvLine[0]));
                        $splitRegions = explode(",", trim($csvLine[1]));
                        $splitPostcodes = explode(",",trim($csvLine[3]));
                        $splitDaysOfWeek 	= explode(",",trim($csvLine[14]));

						if ($csvLine[2] == '*' || $csvLine[2] == '') {
							$city = '';
						} else {
							$city = $csvLine[2];
						}

						if ($csvLine[4] == '*' || $csvLine[4] == '') {
							$zip_to = '';
						} else {
							$zip_to = $csvLine[4];
						}


						if ($csvLine[5] == '*' || $csvLine[5] == '') {
							$package_id = '';
						} else {
							$package_id = $csvLine[5];
						}

                    	if ( $csvLine[6] == '*' || $csvLine[6] == '') {
							$weight_from = -1;
						} else if (!$this->_isPositiveDecimalNumber($csvLine[6])) {
							$exceptions[] = Mage::helper('shipping')->__('Invalid weight From "%s" in the Row #%s', $csvLine[6], ($k+1));
                    	} else {
							$weight_from = (float)$csvLine[6];
						}

						if ( $csvLine[7] == '*' || $csvLine[7] == '') {
							$weight_to = 10000000;
						} else if (!$this->_isPositiveDecimalNumber($csvLine[7])) {
							$exceptions[] = Mage::helper('shipping')->__('Invalid weight To "%s" in the Row #%s', $csvLine[7], ($k+1));
						}
						else {
							$weight_to = (float)$csvLine[7];
						}

						if ( $csvLine[8] == '*' || $csvLine[8] == '') {
							$price_from = -1;
						} else if (!$this->_isPositiveDecimalNumber($csvLine[8]) ) {
							$exceptions[] = Mage::helper('shipping')->__('Invalid price From "%s" in the Row #%s',  $csvLine[8], ($k+1));
						} else {
							$price_from = (float)$csvLine[8];
						}

						if ( $csvLine[9] == '*' || $csvLine[9] == '') {
							$price_to = 10000000;
						} else if (!$this->_isPositiveDecimalNumber($csvLine[9])) {
							$exceptions[] = Mage::helper('shipping')->__('Invalid price To "%s" in the Row #%s', $csvLine[9], ($k+1));
						} else {
							$price_to = (float)$csvLine[9];
						}

						if ( $csvLine[10] == '*' || $csvLine[10] == '') {
							$item_from = 0;
						} else if (!$this->_isPositiveDecimalNumber($csvLine[10]) ) {
							$exceptions[] = Mage::helper('shipping')->__('Invalid item From "%s" in the Row #%s',  $csvLine[10], ($k+1));
						} else {
							$item_from = (float)$csvLine[10];
						}

						if ( $csvLine[11] == '*' || $csvLine[11] == '') {
							$item_to = 10000000;
						} else if (!$this->_isPositiveDecimalNumber($csvLine[11])) {
							$exceptions[] = Mage::helper('shipping')->__('Invalid item To "%s" in the Row #%s', $csvLine[11], ($k+1));
						} else {
							$item_to = (float)$csvLine[11];
						}

						if($volumeSwitch){
						    if ( $csvLine[12] == '*' || $csvLine[12] == '') {
						        $volume_from = -1;
						    } else if (!$this->_isPositiveDecimalNumber($csvLine[12])) {
						        $exceptions[] = Mage::helper('shipping')->__('Invalid volume From "%s" in the Row #%s', $csvLine[12], ($k+1));
						    } else {
						        $volume_from = (float)$csvLine[12];
						    }

						    if ( $csvLine[13] == '*' || $csvLine[13] == '') {
						        $volume_to = 10000000;
						    } else if (!$this->_isPositiveDecimalNumber($csvLine[13])) {
						        $exceptions[] = Mage::helper('shipping')->__('Invalid volume To "%s" in the Row #%s', $csvLine[13], ($k+1));
						    }
						    else {
						        $volume_to = (float)$csvLine[13];
						    }

						    if ($csvLine[14] == '*' || $csvLine[14] == '') {
						        $customer_group = '';
						    } else {
						        $customer_group = $csvLine[14];
						    }
						} else {
						    if ($csvLine[12] == '*' || $csvLine[12] == '') {
						        $customer_group = '';
						    } else {
						        $customer_group = $csvLine[12];
						    }
						}

                      	foreach ($splitCountries as $country) {

                      		$country=trim($country);

                        	if (empty($countryCodesToIds) || !array_key_exists($country, $countryCodesToIds)) {
	                        	$countryId = '0';
	                            if ($country != '*' && $country != '') {
	                                $exceptions[] = Mage::helper('shipping')->__('Invalid Country "%s" in the Row #%s', $country, ($k+1));
	                                break;
	                            }
	                        } else {
	                            $countryId = $countryCodesToIds[$country];
	                        }

                        	foreach ($splitRegions as $region) {

                        		if (!isset($countryCodesToIds[$country])
		                            || !isset($regionCodesToIds[$countryCodesToIds[$country]])
		                            || !array_key_exists($region, $regionCodesToIds[$countryCodesToIds[$country]])) {
		                            $regionId = '0';
			                        if ($region != '*' && $region != '') {
		                            	$exceptions[] = Mage::helper('shipping')->__('Invalid Region/State "%s" in the Row #%s', $region, ($k+1));
		                            	break;
		                            }
		                        } else {
		                            $regionId = $regionCodesToIds[$countryCodesToIds[$country]][$region];
		                        }

                        		foreach ($splitPostcodes as $postcode) {
									if ($postcode == '*' || $postcode == '') {
										$zip = '';
										$new_zip_to = '';
									} else {
										$zip_str = explode("-", $postcode);
										if(count($zip_str) != 2)
										{
											$zip = trim($postcode);
											if (ctype_digit($postcode)) {
												$new_zip_to = trim($postcode);
											} else $new_zip_to = $zip_to;
										}
										else {
											$zip = trim($zip_str[0]);
											$new_zip_to = trim($zip_str[1]);
										}
									}

                        			foreach ($splitDaysOfWeek as $dayOfWeek) {

			                        	//if ($csvLine[3] == '*' || $csvLine[3] == '') {
										if ($dayOfWeek == '*' || $dayOfWeek == '') {
											$day = '';
										} else {
											//$zip = $csvLine[3];
											$day = $dayOfWeek;
										}

										if($volumeSwitch){
										    $data[] = array('website_id'=>$websiteId, 'dest_country_id'=>$countryId, 'dest_region_id'=>$regionId,
										            'dest_city'=>$city, 'dest_zip'=>$zip, 'dest_zip_to'=>$new_zip_to,
										            'package_id'=>$package_id,
										            'weight_from_value'=>$weight_from,'weight_to_value'=>$weight_to,
										            'price_from_value'=>$price_from,'price_to_value'=>$price_to,
										            'item_from_value'=>$item_from,'item_to_value'=>$item_to,
										            'volume_from_value'=>$volume_from,'volume_to_value'=>$volume_to,
										            'customer_group'=>$customer_group,
										            'price'=>$csvLine[15],'day'=>$day,  'algorithm'=>$csvLine[17], 'delivery_type'=>$csvLine[18], 'notes'=>$csvLine[19]);
										} else {
										    $data[] = array('website_id'=>$websiteId, 'dest_country_id'=>$countryId, 'dest_region_id'=>$regionId,
										            'dest_city'=>$city, 'dest_zip'=>$zip, 'dest_zip_to'=>$new_zip_to,
										            'package_id'=>$package_id,
										            'weight_from_value'=>$weight_from,'weight_to_value'=>$weight_to,
										            'price_from_value'=>$price_from,'price_to_value'=>$price_to,
										            'item_from_value'=>$item_from,'item_to_value'=>$item_to,
										            'customer_group'=>$customer_group,
										            'price'=>$csvLine[13],'day'=>$day,  'algorithm'=>$csvLine[15], 'delivery_type'=>$csvLine[16], 'notes'=>$csvLine[17]);
										}
										$dataDetails[] = array('country'=>$country, 'region'=>$region);
                        			}
									$counter++;
	                        	}
                        	}
                        }

                        $dataStored = false;
                   		if (!empty($exceptions)) {
			            	break;
			            }
	                    if ($counter>3000) {
		                    foreach($data as $eKey=>$dataLine) {
		                        try {
		                            $connection->insert($table, $dataLine);
		                        } catch (Exception $e) {
		                            $exceptions[] = Mage::helper('shipping')->__('Duplicate Row #%s (Country "%s", Region/State "%s", Zip "%s")', ($eKey+1), $dataLine['dest_country_id'], $dataLine['dest_region_id'], $dataLine['dest_zip']);
		                            $exceptions[] = $e;
		                       }
		                    }
		                    if (!empty($exceptions)) {
			                	break;
			                }
	                		Mage::helper('wsacommon/shipping')->updateStatus($session,count($data));
			               	unset($data);
			                unset($dataDetails);
			                $counter=0;
			                $dataStored = true;
		                }
                    }
                }
                if (empty($exceptions) && !$dataStored) {
                	foreach($data as $k=>$dataLine) {
			           try {
			           	$connection->insert($table, $dataLine);
			           } catch (Exception $e) {
			           	$exceptions[] = Mage::helper('shipping')->__('Duplicate Row #%s (Country "%s", Region/State "%s", Zip "%s")', ($k+1), $dataDetails[$k]['country'], $dataDetails[$k]['region'], $dataLine['dest_zip']);
			            $exceptions[] = $e;
			            break;
			           }
	                }
	                Mage::helper('wsacommon/shipping')->updateStatus($session,count($data));

                }
            	if (!empty($exceptions)) {
            		throw new Exception( "\n" . implode("\n", $exceptions) );
                }
            }
        }
    }

    private function _getCsvValues($string, $separator=",")
    {
        $elements = explode($separator, trim($string));
        for ($i = 0; $i < count($elements); $i++) {
            $nquotes = substr_count($elements[$i], '"');
            if ($nquotes %2 == 1) {
                for ($j = $i+1; $j < count($elements); $j++) {
                    if (substr_count($elements[$j], '"') > 0) {
                        // Put the quoted string's pieces back together again
                        array_splice($elements, $i, $j-$i+1, implode($separator, array_slice($elements, $i, $j-$i+1)));
                        break;
                    }
                }
            }
            if ($nquotes > 0) {
                // Remove first and last quotes, then merge pairs of quotes
                $qstr =& $elements[$i];
                $qstr = substr_replace($qstr, '', strpos($qstr, '"'), 1);
                $qstr = substr_replace($qstr, '', strrpos($qstr, '"'), 1);
                $qstr = str_replace('""', '"', $qstr);
            }
            $elements[$i] = trim($elements[$i]);
        }
        return $elements;
    }

    private function _isPositiveDecimalNumber($n)
    {
        return preg_match ("/^[0-9]+(\.[0-9]*)?$/", $n);
    }
    public function getShippingCostsforSKU($sku)
    {
       $collection = Mage::getResourceModel('matrixdays_shipping/carrier_matrixdays_collection');
       return $collection->getSkuCosts($sku, $collection);

    }

    private function createDataGrid($rates,&$earliestExpectedDelivery) {

    	if ($this->_debug) {
			Mage::helper('wsacommon/log')->postNotice('matrixdays','Datagrid Rates',$rates);
    	}
    	$switchOverDay=-1;
 		$switchOverPrice=-1;
 		$resultSet=array();

 		$multipleRates=false;

 		switch (count($rates)) {
 			case 1:
    			$rate = $rates[0];
 				break;
 			case 2:
 				//TODO Add switch here, so can optionally show both

	 			// tile change, if more than 1 rate then take cheapest when available
	    		foreach ($rates as $key=>$checkRate) {
	    			if ($key==0) {
	    				$methodName = $checkRate['method_name'];
	    				$rate = $checkRate;
	    			} else {
		    			if ($methodName!=$checkRate['method_name']) {
		    				$multipleRates=true;
		    				break;
		    			}
		    			if (strtotime($checkRate['expected_delivery'])<strtotime($rate['expected_delivery'])) {
	    					if ($rate['price']<$checkRate['price']) {
	    						// is a cheaper one
	    						$switchOverDay=$rate['expected_delivery'];
	    						$switchOverPrice=$rate['price'];
	    					}
	    					$rate = $checkRate;
		    			}
	    			}
	    		}
	    		if ($switchOverDay==-1) {
 					$multipleRates=true;
	    		}
	 		   	if ($this->_debug) {
					Mage::helper('wsacommon/log')->postNotice('matrixdays','Switchover Day',$switchOverDay);
					Mage::helper('wsacommon/log')->postNotice('matrixdays','Switchover Price',$switchOverPrice);
		    	}
	    		break;
 			default:
 				$multipleRates=true;
 				break;
 		}

    	$this->_defaultShipRate 	= Mage::helper('matrixdays')->getDefaultPrice();
    	$this->_defaultSlots 		= Mage::helper('matrixdays')->getDefaultSlots();

 		if ($multipleRates) {
 			if ($this->_debug) {
				Mage::helper('wsacommon/log')->postNotice('matrixdays','Found multiple rates','');
 			}
 			$count=0;
 			foreach ($rates as $rate) {
 				$count++;
 				if ($count>5) { break; }
 				$this->getCalendarRates($resultSet, $earliestExpectedDelivery, $rate,-1,-1);
 			}
 		} else {
 			$this->getCalendarRates($resultSet, $earliestExpectedDelivery, $rate,$switchOverDay,$switchOverPrice);
 		}

 		if ($this->_debug) {
    		Mage::helper('wsacommon/log')->postNotice('matrixdays','Result Set',$resultSet);
    	}
 		return $resultSet;

    }

    protected function getCalendarRates(&$resultSet,&$earliestExpectedDelivery, $rate,$switchOverDay,$switchOverPrice) {

    	if (!array_key_exists('expected_delivery', $rate)) {
    		return;
    	}
    	$savedBaseRate 				= $rate['price'];
    	$expectedDeliveryDate 		= $rate['expected_delivery'];
    	$deliveryType 				= $rate['delivery_type'];
    	$expectedDeliveryDay 		= $rate['delivery_day'];
    	$allowedSlot				= $rate['slot'];
    	$numDeliveryDays			= $rate['num_delivery_days'];
        $todaysDate                 = date(Mage::helper('matrixdays')->getDateFormat(), Mage::getModel('core/date')->timestamp(time()));
        $sameDayDeliveryOnly		= $rate['same_day'];

    	$dateFormat 				= Mage::helper('matrixdays')->getDateFormat();
    	$blackoutDays 				= Mage::helper('matrixdays')->getBlackoutDeliveryDays();
    	$blackoutDates 				= Mage::helper('matrixdays')->getBlackoutDeliveryDates();
   		$numTimeSlots 				= Mage::helper('matrixdays')->getNumTimeSlots();
   		$methodName					= $rate['method_name'];

    	if ($this->_debug) {
			Mage::helper('wsacommon/log')->postNotice('matrixdays','Default Ship Rate',$this->_defaultShipRate);
			Mage::helper('wsacommon/log')->postNotice('matrixdays','BlackoutDays',$blackoutDays);
			Mage::helper('wsacommon/log')->postNotice('matrixdays','BlackoutDates',$blackoutDates);
			Mage::helper('wsacommon/log')->postNotice('matrixdays','Expected Delivery Date',$expectedDeliveryDate);
			Mage::helper('wsacommon/log')->postNotice('matrixdays','Expected Delivery Day',$expectedDeliveryDay);
    	}



    	// remove blackout delivery days of week
    	// remove blackout delivery dates of week

		// get 4 weeks worth of rates
  		$baseRate=$savedBaseRate;
    	$numWeeks = Mage::helper('matrixdays')->getNumOfWeeks();

		$tempExpectedDeliveryDate = $expectedDeliveryDate;
		for ($week=0;$week<$numWeeks;$week++) {
			if ($switchOverDay!=-1 && $baseRate==$switchOverPrice) {
				// dont want to change back price
				$switchOverDay=-1;
			}
			for ($j=0;$j<$numTimeSlots;$j++) {
				if(count($allowedSlot) > 0 && !in_array($j+1, $allowedSlot)){
					continue;
				}
				if ($switchOverDay!=-1) {
					$switched = false;
					$baseRate=$savedBaseRate;
				} else {
					$switched = true;
				}
                $newExpectedDeliveryDate = $tempExpectedDeliveryDate;
                $timeGrid = $this->getTimeGrid($newExpectedDeliveryDate,$j);
				if (is_array($timeGrid) && count($timeGrid)>0) {
	    			$empty=false;
	    			$slotData = $timeGrid[0];
				} else {
					$empty=true;
				}

	    		for ($i=0;$i<7;$i++) {
	    			if (!$switched && $switchOverDay!=-1 && $switchOverDay==$newExpectedDeliveryDate) {
	    				$switched=true;
	    				$baseRate = $switchOverPrice;
	    			}
	    			$currDay = ($i+$expectedDeliveryDay) % 7;
	    			if ($currDay==0) {
	    				$currDay=7;
	    			}

	    			if (in_array($currDay,$blackoutDays) || in_array($newExpectedDeliveryDate,$blackoutDates)) {
		        		$price = -1;
		        	} else if ($empty) {
		        		$price = $baseRate+$this->_defaultShipRate;
		        	} else if (empty($slotData[$currDay.'_slots'])) {
		        		$price = -1;
                        Mage::helper('matrixdays')->addBlackoutDeliveryDate($newExpectedDeliveryDate);
		        	} else if ($slotData[$currDay.'_price'] == -1) {
		        		$price = -1;
                        Mage::helper('matrixdays')->addBlackoutDeliveryDate($newExpectedDeliveryDate);
                    } else {
		        		$price = $baseRate+$slotData[$currDay.'_price'];
		        	}
		        	if ($price!=-1) {
		        		if (in_array($currDay,$rate['excl']) ||
		        			(count($rate['incl'])>0 && !in_array($currDay,$rate['incl']))) {
		        			$price=-1;
                            Mage::helper('matrixdays')->addBlackoutDeliveryDate($newExpectedDeliveryDate);
                        }

		        	}

                    $dispatch = Mage::helper('matrixdays')->
                        getRollbackDispatchDate($newExpectedDeliveryDate,$numDeliveryDays,$dateFormat);

                    $time = ' '.Mage::getStoreConfig('carriers/matrixdays/cutoff_time');

                    if(strtotime($dispatch.$time) < strtotime($time)) {
                        Mage::log($dispatch);Mage::log("dispatchBad");
                        $price = -1;
                        Mage::helper('matrixdays')->addBlackoutDeliveryDate($newExpectedDeliveryDate);
                    }

                    if ($price!=-1 && (!$sameDayDeliveryOnly || ($sameDayDeliveryOnly && $newExpectedDeliveryDate== $todaysDate))) {
		        	    if (in_array('exclude_shipping_method',$this->_options)) {
		        		    $methodDescription = Mage::helper('matrixdays')->getTimeSlotArr($j);
		        		    $deliveryDisplay   = $newExpectedDeliveryDate.' - '.Mage::helper('matrixdays')->getTimeSlotArr($j);
		        	    } else {
		        		    $methodDescription = $deliveryType.' - '.Mage::helper('matrixdays')->getTimeSlotArr($j);
		        		    $deliveryDisplay   = $newExpectedDeliveryDate.' - '.$deliveryType.' - '.Mage::helper('matrixdays')->getTimeSlotArr($j);
		        	    }

		        	    $methodName=preg_replace('/&|;| /',"_",$methodName);
		        	    $delCode = $methodName.'_'.$j.'_'.$newExpectedDeliveryDate;

	    			    $resultSet[]= array (
	        			    'price' 			=> 	$price,
	        			    'code' 				=> 	$delCode,
	    				    'delivery_type'		=> 	$deliveryDisplay,
	    				    'method_name'		=>  $methodName,
	    				    'method_description'=>  $methodDescription,
	    				    'expected_delivery' =>	$newExpectedDeliveryDate,
	    				    'dispatch_date'		=>  $newExpectedDeliveryDate
                            //'dispatch_date'		=>  $dispatch
	    			    );

	    			    if ($earliestExpectedDelivery==-1 || strtotime($earliestExpectedDelivery) > strtotime($newExpectedDeliveryDate)) {
	    				    $earliestExpectedDelivery=$newExpectedDeliveryDate;
	    			    }
                    } //else { Mage::log($methodName);Mage::log($dispatch);Mage::log($price);}

	    			$newExpectedDeliveryDate = date($dateFormat,strtotime($newExpectedDeliveryDate . ' +1 day'));  //TODO reduce calculation to just one loop

	    		}
	    	}
	    	$tempExpectedDeliveryDate = date($dateFormat,strtotime($tempExpectedDeliveryDate . ' +1 week'));

		}

    }

    /**
     * Get time grid date with prices & slots from database
     * Could go over 2 weeks
     * @param $startDate
     * @param $slot
     * @return Array of timegrid results
     */
    private function getTimeGrid($startDate,$slot) {
    	$dayOfWeek = date("w",strtotime($startDate));
    	$dayOfWeek = $dayOfWeek == 0 ? 7 : $dayOfWeek;

    	if ($dayOfWeek==1) { // monday
    	  	$collection = Mage::getModel('timegrid/timegrid')->getCollection()
				->setWeekCommencing($startDate)
				->setSlot($slot);
    		$timeGrid = $collection->getData();

	    	if (count($timeGrid)==0) {
	    		// default slot
	    		return $this->getTimeGridData($slot);
	    	}
	    	return $timeGrid;

    	} else {
    		$prevMonday = Mage::helper('matrixdays')->getPreviousMonday($startDate);
       		$prevMonTimeGrid = $this->getTimeGridData($slot,$prevMonday);
    		if (count($prevMonTimeGrid)==0) {
	     		$nextMonday = date('Y-m-d',strtotime($prevMonday . "+7 days")) ;
	    		$nextMonTimeGrid = $this->getTimeGridData($slot,$nextMonday);
	    		if (count($nextMonTimeGrid)==0) {
		    		return $this->getTimeGridData($slot);
		    	}
		    	$timeGrid = $this->getTimeGridData($slot); // default slot
		    	$innerTimeGrid = $nextMonTimeGrid[0];
		    	for ($i=1;$i<$dayOfWeek;$i++) {
		    		$timeGrid[0][$i.'_price'] = $innerTimeGrid[$i.'_price'];
		    		$timeGrid[0][$i.'_slots'] = $innerTimeGrid[$i.'_slots'];
		    	}
		    	return $timeGrid;
    		} else {
      			// have found one matching timegrid
      			// populate those slots that are in
      			$nextMonday = date('Y-m-d',strtotime($prevMonday . "+7 days")) ;
    			$nextMonTimeGrid = $this->getTimeGridData($slot,$nextMonday);

	    		if (count($nextMonTimeGrid)==0) {
	    			$timeGrid = $this->getTimeGridData($slot); // default
			    	$innerTimeGrid = $prevMonTimeGrid[0];
	    			for ($i=$dayOfWeek;$i<8;$i++) {
			    		$timeGrid[0][$i.'_price'] = $innerTimeGrid[$i.'_price'];
			    		$timeGrid[0][$i.'_slots'] = $innerTimeGrid[$i.'_slots'];
		    		}
		    		return $timeGrid;
		    	} else {
		    		$timeGrid = $prevMonTimeGrid;
			    	$innerTimeGrid = $nextMonTimeGrid[0];
			    	for ($i=1;$i<$dayOfWeek;$i++) {
			    		$timeGrid[0][$i.'_price'] = $innerTimeGrid[$i.'_price'];
			    		$timeGrid[0][$i.'_slots'] = $innerTimeGrid[$i.'_slots'];
			    	}
			    	return $timeGrid;
		    	}
      		}
    	}
    }

    	// cant find date, use default. If this isnt there will blow at present TODO
    private function getTimeGridData($slot,$date = '0000-00-00') {
	    $collection = Mage::getModel('timegrid/timegrid')->getCollection()
			->setWeekCommencing($date)
			->setSlot($slot);
		$data=$collection->getData();
		if (empty($data) && $date=='0000-00-00') {
			$data[] = array (
				'timegrid_id' => 4,
	            'week_commencing' => 0000-00-00,
	            'time_slot_id' => 2,
	            '1_price' => $this->_defaultShipRate,
	            '2_price' => $this->_defaultShipRate,
	            '3_price' => $this->_defaultShipRate,
	            '4_price' => $this->_defaultShipRate,
	            '5_price' => $this->_defaultShipRate,
	            '6_price' => $this->_defaultShipRate,
	            '7_price' => $this->_defaultShipRate,
	            '1_slots' => $this->_defaultSlots,
	            '2_slots' => $this->_defaultSlots,
	            '3_slots' => $this->_defaultSlots,
	            '4_slots' => $this->_defaultSlots,
	            '5_slots' => $this->_defaultSlots,
	            '6_slots' => $this->_defaultSlots,
	            '7_slots' => $this->_defaultSlots,
        	);
		}
	    return $data;
    }

}
