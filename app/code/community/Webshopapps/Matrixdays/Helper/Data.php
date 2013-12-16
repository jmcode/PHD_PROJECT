<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales module base helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Webshopapps_Matrixdays_Helper_Data extends Mage_Core_Helper_Data
{
	private static $_blackoutProductionDays;
	private static $_blackoutProductionDates;
	private static $_blackoutDeliveryDates;
	private static $_blackoutDeliveryDays;
	private static $_dateFormat;
	private static $_gridType;
	private static $_dateFormatString;
	private static $_numOfWeeks;
    private static $_numOfDatesAtCheckout;
    private static $_debug;

    public static function isDebug()
    {
        if (self::$_debug==NULL) {
            self::$_debug = Mage::helper('wsalogger')->isDebug('Webshopapps_Matrixdays');
        }
        return self::$_debug;
    }

	public static function resetStatics() {
		self::$_blackoutProductionDays = null;
		self::$_blackoutProductionDates = null;
		self::$_blackoutDeliveryDates = null;
		self::$_blackoutDeliveryDays = null;
		self::$_dateFormat = null;
		self::$_gridType = null;
		self::$_dateFormatString = null;
		self::$_numOfWeeks = null;
        self::$_numOfDatesAtCheckout = null;
	}

    public function zipCodeLength($request) {
        $zipcodeMaxLength = Mage::getStoreConfig('carriers/matrixdays/zipcode_max_length');
        if(empty($zipcodeMaxLength)) {
		    if ( Mage_Usa_Model_Shipping_Carrier_Abstract::USA_COUNTRY_ID == $request->getDestCountryId()) {
                $splitPostcode = explode('-',$request->getDestPostcode());
                $postcode = $splitPostcode[0];
		    } else if ( 'BR' == $request->getDestCountryId()) {
                $postcode = str_replace("-","",$request->getDestPostcode());
		    } else {
                $postcode = $request->getDestPostcode();
		    }
    	} else {
            $postcode = substr($request->getDestPostcode(), 0, $zipcodeMaxLength);
		}

        return $postcode;
    }

	public static function getDateFormat() {
		if (self::$_dateFormat==NULL) {
			self::$_dateFormat = Mage::getModel('matrixdays/carrier_source_dateformat')->getDestString(
				Mage::getStoreConfig('carriers/matrixdays/date_format'));
		}
		return self::$_dateFormat;
	}

	public static function getDateFormatString() {
		switch (self::getDateFormat()){
			case 'd-m-Y': self::$_dateFormatString = 'dd-mm-yy'; break;
			case 'm/d/Y': self::$_dateFormatString = 'mm/dd/yy'; break;
			case 'D d-m-Y': self::$_dateFormatString = 'D dd-mm-yy'; break;
			default: self::$_dateFormatString = 'dd-mm-yy'; break;
		}
		return self::$_dateFormatString;
	}

	public static function getBlackoutProductionDays() {
		if (self::$_blackoutProductionDays==NULL) {
			$configProductionDays = Mage::getStoreConfig('carriers/matrixdays/production_daysofweek');
			self::$_blackoutProductionDays = $configProductionDays==null ? array() : explode(",",$configProductionDays);
		}
		return self::$_blackoutProductionDays;
	}

	public static function getBlackoutProductionDates() {
		if (self::$_blackoutProductionDates==NULL) {
			$configProductionDates = Mage::getStoreConfig('carriers/matrixdays/production_dates');
			self::$_blackoutProductionDates = $configProductionDates==null ? array() : explode(",",$configProductionDates);
		}
		return self::$_blackoutProductionDates;
	}

	public static function getBlackoutDeliveryDays() {
		if (self::$_blackoutDeliveryDays==NULL) {
			$configDeliveryDays = Mage::getStoreConfig('carriers/matrixdays/delivery_daysofweek');
			self::$_blackoutDeliveryDays = $configDeliveryDays==null ? array() : explode(",",$configDeliveryDays);
		}
		return self::$_blackoutDeliveryDays;
	}

	public static function addBlackoutDeliveryDay($day) {
		$dayArr = explode("-", $day);

		if(count($dayArr) > 1) {
			for($i = $dayArr[1];$i>=$dayArr[0];$i--){
				self::$_blackoutDeliveryDays[] = $i;
			}
		}
		else {
			self::$_blackoutDeliveryDays[] = $day;
		}
		array_unique(self::$_blackoutDeliveryDays);
	}

    public static function addBlackoutDeliveryDate($date) {
        self::$_blackoutDeliveryDates[] = $date;

        array_unique(self::$_blackoutDeliveryDates);
    }


	public static function getBlackoutDeliveryDates() {
		if (self::$_blackoutDeliveryDates==NULL) {
			$configDeliveryDates = Mage::getStoreConfig('carriers/matrixdays/delivery_dates');
			self::$_blackoutDeliveryDates = $configDeliveryDates==null ? array() : explode(",",$configDeliveryDates);
		}
		return self::$_blackoutDeliveryDates;
	}


	public function getTimeSlots() {

		$arr = array();
        $arr[] = array('value'=>0, 'label'=>Mage::helper('matrixdays')->__(Mage::getStoreConfig('carriers/matrixdays/slot_1')));
        $arr[] = array('value'=>1, 'label'=>Mage::helper('matrixdays')->__(Mage::getStoreConfig('carriers/matrixdays/slot_2')));
        $arr[] = array('value'=>2, 'label'=>Mage::helper('matrixdays')->__(Mage::getStoreConfig('carriers/matrixdays/slot_3')));
        $arr[] = array('value'=>3, 'label'=>Mage::helper('matrixdays')->__(Mage::getStoreConfig('carriers/matrixdays/slot_4')));
        $arr[] = array('value'=>4, 'label'=>Mage::helper('matrixdays')->__(Mage::getStoreConfig('carriers/matrixdays/slot_5')));
        $arr[] = array('value'=>5, 'label'=>Mage::helper('matrixdays')->__(Mage::getStoreConfig('carriers/matrixdays/slot_6')));
        return $arr;


	}
	public function getTimeSlotOptions() {

		return array (
		 	'0' => Mage::helper('matrixdays')->__(Mage::getStoreConfig('carriers/matrixdays/slot_1')),
            '1' => Mage::helper('matrixdays')->__(Mage::getStoreConfig('carriers/matrixdays/slot_2')),
            '2' => Mage::helper('matrixdays')->__(Mage::getStoreConfig('carriers/matrixdays/slot_3')),
		    '3' => Mage::helper('matrixdays')->__(Mage::getStoreConfig('carriers/matrixdays/slot_4')),
			'4' => Mage::helper('matrixdays')->__(Mage::getStoreConfig('carriers/matrixdays/slot_5')),
			'5' => Mage::helper('matrixdays')->__(Mage::getStoreConfig('carriers/matrixdays/slot_6')),
		);
	}

	public function getTimeSlotArr($code='') {

		$timeSlots=array ();

		for ($i=1;$i<=$this->getNumTimeSlots();$i++) {
			$timeSlots[] = Mage::getStoreConfig('carriers/matrixdays/slot_'.$i);
		};

	 	if (''===$code) {
            return $timeSlots;
        }
        return $timeSlots[$code];
	}

	public function getDefaultPrice() {
		return Mage::getStoreConfig('carriers/matrixdays/default_ship_price');

	}

	public function getDefaultSlots() {
		return Mage::getStoreConfig('carriers/matrixdays/default_avail_slots');
	}

	public function getNumTimeSlots() {
		return Mage::getStoreConfig('carriers/matrixdays/num_slots');
	}



    public function getDispatchDay(&$dayCount,&$dispatchDate, $productionDays = 0, $cutOffTime = -1)
    {
    	$dispatchDate=date("N",Mage::app()->getLocale()->storeTimeStamp());

    	if ($productionDays<=0  && $cutOffTime == -1) {
    		$productionDays = Mage::getStoreConfig('carriers/matrixdays/production_days');
    	}

    	$currentDay=date("N",Mage::app()->getLocale()->storeTimeStamp());
    	$dayCount=0;
    	$this->getPickupDayCount($productionDays,$cutOffTime,$dayCount);  // TODO: Check for true/false
    	$pickupDay=($currentDay+$dayCount) % 7;
    	if ($pickupDay==0) {
    		$pickupDay=7;
    	}

    	if (!$this->getDispatchDayCount($pickupDay,$dayCount)) {
    		return null;
    	}

    	$dispatchDate=$this->getDate($dayCount);



    	return $pickupDay;

    }


    /**
     * returns the date of dispatch given the expected delivery date and number of delivery days
     * Counts backwards
     * Assumes that a blackout production day and a blackout production date mean cant be sent on that date
     * Assumes that a blackout delivery day/date means cant be delivered on that date
     * @param $expectedDeliveryDate
     * @param int $numDeliveryDays
     * @param String $dateFormat
     * @internal param String $newExpectedDeliveryDate
     * @return String $dispatchDate
     */
    public function getRollbackDispatchDate($expectedDeliveryDate,$numDeliveryDays,$dateFormat)
    {
        $expPickupTime = strtotime('-'.$numDeliveryDays.' day', strtotime($expectedDeliveryDate.' '.Mage::getStoreConfig('carriers/matrixdays/cutoff_time')));
        $expPickupDayOfWeek = date("N",$expPickupTime);
        $dayCount = floor(($expPickupTime - time())/(60*60*24));

        if (!$this->getDispatchDayCount($expPickupDayOfWeek,$dayCount,true)) {
            return date($dateFormat,strtotime($expectedDeliveryDate. ' -'.$numDeliveryDays.' day'));
        }

        if (!$this->getFreeDispatchDay($dayCount)) {
            return date($dateFormat,strtotime($expectedDeliveryDate. ' -'.$numDeliveryDays.' day'));
        }

        $dispatchDate=$this->getDate($dayCount);

        return $dispatchDate;
    }
    
    /**
     * Used when reverse finding the dispatch day. Want a clear day which isnt a blackout production day/date
     * @param int $dayCount
     * @return Boolean $validDateFound
     */
    private function getFreeDispatchDay(&$dayCount) {
        
        $i=0;
        $validDateFound=false;
        $first=true;
        $suggestedDispatchDay=$this->getDay($dayCount);
        
        while ($validDateFound==false) {
            $validDateFound=true;
            if (self::getBlackoutProductionDays()!='') {
                if (!$this->getBlackoutDaysCount(self::getBlackoutProductionDays(),$suggestedDispatchDay,$dayCount,$validDateFound,true)) {
                    return false;
                }
            }

            if (self::getBlackoutProductionDates()!='' && ($first || !$validDateFound)) {
                $first=false;
                $suggestedDispatchDay=$this->getDispatchDate(self::getBlackoutProductionDates(),$dayCount,$validDateFound,true);
            } else {
                $validDateFound=true;
            }
            $i++;
            if ($i>21) {
                break;
            }
        }
        
        
        return $validDateFound;
    }
    
    private function getDay($dayCount) {
    	return date("N",Mage::app()->getLocale()->storeTimeStamp()+(86400*$dayCount));
    }


    private  function getDate($dayCount) {
    	return date(self::getDateFormat(),Mage::app()->getLocale()->storeTimeStamp()+(86400*$dayCount));
    }

    private function getDispatchDayCount(&$pickupDay, &$dayCount,$rollback=false) {
     	$validDateFound=false;
	    $first=true;
	    $i=0;
	    while ($validDateFound==false) {
	   		$validDateFound=true;
	    	if (self::getBlackoutDeliveryDays()!='') {
	    		if (!$this->getBlackoutDaysCount(self::getBlackoutDeliveryDays(),$pickupDay,$dayCount,$validDateFound,$rollback)) {
	    			return false;
	    		}
	    	}

	    	if (self::getBlackoutDeliveryDates()!='' && ($first || !$validDateFound)) {
	     		$first=false;
	    		$pickupDay=$this->getDispatchDate(self::getBlackoutDeliveryDates(),$dayCount,$validDateFound,$rollback);
	    	} else {
	    		$validDateFound=true;
	     	}

	    	$i++;
	     	if ($i>21) {
	     		break;
	     	}
	    }
	    return true;
    }


    public function getWorkingDate($dispatchDay,$dispatchDayCount,$numDaysToAdd,&$deliverDay) {
    	$tempDispatchDay=$dispatchDay;
    	$tempDispatchDayCount=$dispatchDayCount;
    	for ($i=0;$i<$numDaysToAdd;$i++) {
    		$tempDispatchDayCount++;
    		$tempDispatchDay=($tempDispatchDay+1) %7;
	    	if ($tempDispatchDay==0) {
		    		$tempDispatchDay=7;
		    }
    		$this->getDispatchDayCount($tempDispatchDay,$tempDispatchDayCount);
    	}
    	$deliverDay=$this->getDay($tempDispatchDayCount);
    	return $this->getDate($tempDispatchDayCount);
    }



     private function getPickupDayCount($productionDays,$cutoffTime = -1, &$dayCount=0, $rollback=false) {

     	if ($cutoffTime == -1) {
     		$cutoffTime = Mage::getStoreConfig('carriers/matrixdays/cutoff_time');
     	}

    	// see if blackout day or date
    	$startProductionDay=date("N",Mage::app()->getLocale()->storeTimeStamp());
    	if (!$this->getProductionDay($startProductionDay,$dayCount,$rollback)) {
    		return false;
    	}

    	if ($dayCount==0 && !empty($cutoffTime)) {
    		$cutoffArr=explode(":",$cutoffTime);
    		if (count($cutoffArr)==2) {
    			$cutoffMins=$cutoffArr[0]*60+$cutoffArr[1];
    			$time=date("G:i",Mage::app()->getLocale()->storeTimeStamp());
    			$timeArr=explode(":",$time);
    			if (count($timeArr)==2) {
    				$timeNowMins=$timeArr[0]*60+$timeArr[1];
    				if ($timeNowMins>$cutoffMins) {
    					$dayCount++;
    				}
    			}
    		}
    	}

     	if (!$this->getProductionDay($startProductionDay,$dayCount,$rollback)) {
    		return false;
    	}

    	for ($i=0;$i<$productionDays;$i++) {
    		$dayCount++;
	    	if (!$this->getProductionDay($startProductionDay,$dayCount,$rollback)) {
	    		return false;
	    	}
    	}

    	return true;
    }

    private function getProductionDay($productionDay,&$dayCount,$rollback=false) {

    	$productionDay=$productionDay+$dayCount % 7;
	    if ($productionDay==0) {
	    		$productionDay=7;
	    }
    	$validDateFound=false;
    	$first=true;
    	$i=0;
	    while ($validDateFound==false) {
	    	$validDateFound=true;
	     	if (self::getBlackoutProductionDays()!='') {
	    		if (!$this->getBlackoutDaysCount(self::getBlackoutProductionDays(),$productionDay,$dayCount,$validDateFound,$rollback)) {
	    			return false;
	    		}
	    	}

	     	if (self::getBlackoutProductionDates()!='' && ($first || !$validDateFound)) {
	     		$first=false;
	    		$productionDay=$this->getDispatchDate(self::getBlackoutProductionDates(),$dayCount,$validDateFound,$rollback);
	     	} else {
	    		$validDateFound=true;
	     	}
	     	$i++;
	     	if ($i>21) {
	     		break;
	     	}
	    }
	    return true;
    }

    private function getBlackoutDaysCount($blackDaysArr,&$day,&$dayCount,&$validDateFound,$rollback=false) {

    	while (true) {
	    	if (in_array($day,$blackDaysArr)) {
	    		$day = $rollback ? ($day-1)%7 : ($day+1)%7;
    			if ($day==0) {
    				$day=7;
    			}
	    		$dayCount = $rollback ? $dayCount-1 : $dayCount+1;
	    		if ($dayCount>200 || $dayCount < -200) {
 	    			return false;
	    		}
	    		$validDateFound=false;
	    	}  else {
    			break;
	    	}
    	}

    	return true;
    }
    
    
    /**
     *   TODO Unwind this as isnt a date, is a day. Too much reuse of same method names
     * @param Array $blackoutDatesArr
     * @param String $dayCount
     * @param Boolean $validDateFound
     * @param Boolean $rollback
     * @return string
     */
    private function getDispatchDate($blackoutDatesArr,&$dayCount,&$validDateFound,$rollback = false) {
        $dateFormat = self::getDateFormat();
        $pickupDay=date($dateFormat,Mage::app()->getLocale()->storeTimeStamp()+(86400*$dayCount));
	    //$usTime=date('m/d/Y',Mage::app()->getLocale()->storeTimeStamp()+(86400*$dayCount));

        $validDateFound=true;

    	$reset = true;
    	while ($reset) {
    		$reset = false;
	        foreach ($blackoutDatesArr as $blackoutDate) {
	        	if ($blackoutDate==$pickupDay) {
	        		$dayCount = $rollback ? $dayCount-1 : $dayCount+1;
	        		$pickupDay=date($dateFormat,Mage::app()->getLocale()->storeTimeStamp()+(86400*$dayCount));
	        		//$usTime=date('m/d/Y',Mage::app()->getLocale()->storeTimeStamp()+(86400*$dayCount));
	        		$reset=true;
	        		$validDateFound=false;
	        	}
	        }
        }
	  return date("N",strtotime($pickupDay));
    }

    public function reduceAvailSlot($observer) {
    	$orders = $observer->getEvent()->getOrder();
    	if (!is_object($orders)) {
    		$orders = $observer->getEvent()->getOrders();
    		if(!is_array($orders)){
    			return;
    		}
    	}

    	!is_array($orders) ? $orders = array($orders) : false;

    	foreach($orders as $order){
	    	$shippingMethod = $order->getShippingMethod();
	    	$parts = explode('_',$shippingMethod);
            $partsCounter = count($parts)-1; //Lets use this as a index

	    	if ($partsCounter < 2 || $parts[0]!='matrixdays') {
	    		return;
	    	}
	    	$slot=$parts[$partsCounter-1];
	    	$date=$parts[$partsCounter];
            $day=date('N',strtotime($date));

	    	$expectedDeliveryDate = $order->getExpectedDelivery();
	    	if ($expectedDeliveryDate=='') {
	    		return;
	    	}
	    	$previousMonday=$this->getPreviousMonday($expectedDeliveryDate);
	    	$collection = Mage::getModel('timegrid/timegrid')->getCollection()
				->setSlot($slot)
				->setWeekCommencing($previousMonday);
			$noTimeSlot=false;
			$timeGrid = $collection->getData();

			if (count($timeGrid)<1) {
				// create new one using default
				$collection = Mage::getModel('timegrid/timegrid')->getCollection()
					->setSlot($slot)
					->setWeekCommencing('0000-00-00');
				$noTimeSlot=true;
				$timeGrid = $collection->getData();
			}

			// update available slot
	    	if ($this->isDebug()) {
	    		Mage::helper('wsacommon/log')->postNotice('matrixdays','Order Shipping Method',$shippingMethod);
	    		Mage::helper('wsacommon/log')->postNotice('matrixdays','Order Available Slots',$timeGrid[0][$day.'_slots']);
	    	}

	    	if(!is_array($timeGrid)) return;

	    	$id=$timeGrid[0]['timegrid_id'];
	    	$timeSlotModel = Mage::getModel('timegrid/timegrid');
	    	$timeSlotModel->load($id);
	    	if (!is_object($timeSlotModel)) {
	    		return;
	    	}

            //No limit set
            if($timeSlotModel[$day.'_slots'] == -1) return;

	    	$timeSlotModel[$day.'_slots']=$timeSlotModel[$day.'_slots']-1;

	    	if ($noTimeSlot) {
	    		$model = Mage::getModel('timegrid/timegrid');
	    		$model->setData($timeSlotModel->getData());
	    		$model->setTimegridId();
	    		$model['week_commencing'] = $previousMonday;

				$model->save();
	    	} else {
	    		$id=$timeGrid[0]['timegrid_id'];
		    	$timeSlotModel = Mage::getModel('timegrid/timegrid');
		    	$timeSlotModel->load($id);
		    	if (!is_object($timeSlotModel)) {
		    		return;
		    	}
		    	$timeSlotModel[$day.'_slots']=$timeSlotModel[$day.'_slots']-1;
	    		$timeSlotModel->setId($id)->save();
	    	}
    	}
    }

    public function getPreviousMonday($date) {
    	$dayofWeek = date("w",strtotime($date));
        if ($dayofWeek == 0) {
    			$adjuster = 6;
		} else {
   			$adjuster = $dayofWeek - 1;
		}
        return date('Y-m-d',strtotime($date . "-" .$adjuster. " days"));
    }


    public function getUsingGrid() {
        return ($this->getGridType()!='bullet');
    }

    public function getGridType() {
    	if (self::$_gridType==NULL) {
    		$options = explode(',',Mage::getStoreConfig("carriers/matrixdays/ship_options"));
    		if (in_array('display_matrix', $options)) {
				self::$_gridType = 'grid';
    		}  else {
    			self::$_gridType = 'bullet';
    		}
		}
		return self::$_gridType;
    }

    public static function getNumOfWeeks() {
    	if (self::$_numOfWeeks==NULL) {
    		self::$_numOfWeeks = Mage::getStoreConfig('carriers/matrixdays/num_of_weeks');
    	}
    	return self::$_numOfWeeks;
    }

    public static function getNumOfDatesAtCheckout() {
    	if (self::$_numOfDatesAtCheckout==NULL) {
    		self::$_numOfDatesAtCheckout = Mage::getStoreConfig('carriers/matrixdays/dates_at_checkout');
    	}
    	return self::$_numOfDatesAtCheckout;
    }
}