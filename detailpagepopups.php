<?php
require_once('app/Mage.php');
Mage::app();

$customerId = $_GET['cust_id'];
if ($_GET['exist'] == 'yes') {
   if ($customerId != "") {
      $user_reg_info = getUsersInfo($customerId);
      if ($user_reg_info == 'false') {
         $product_id = $_GET['pid'];
         $region_id = $_POST['region_name'];
         $sub_reg_id = $_POST['sub_reg'];
         Mage::getModel('core/cookie')->set('region', $region_id, 0, '/');
         Mage::getModel('core/cookie')->set('sub_region', $sub_reg_id, 0, '/');
         if (isset($_POST['opt_id1']) && isset($_POST['cust_opt_id1'])) {
            $_bundle_item_id = $_POST['opt_id1'];
            $cust_opt_id = $_POST['cust_opt_id1'];
         }
         if (isset($_POST['opt_id2']) && isset($_POST['cust_opt_id2'])) {
            $_bundle_item_id = $_POST['opt_id2'];
            $cust_opt_id = $_POST['cust_opt_id2'];
         }
         if (isset($_POST['opt_id3']) && isset($_POST['cust_opt_id3'])) {
            $_bundle_item_id = $_POST['opt_id3'];
            $cust_opt_id = $_POST['cust_opt_id3'];
         }
         if (isset($_POST['opt_id4']) && isset($_POST['cust_opt_id4'])) {
            $_bundle_item_id = $_POST['opt_id4'];
            $cust_opt_id = $_POST['cust_opt_id4'];
         }
         saveUserInfo($customerId, $region_id, $sub_reg_id);
      } else {
         $user_reg_info = getUsersInfo($customerId);
         $reginfo = explode(",", $user_reg_info);
         $region_id = $reginfo[0];
         $sub_reg_id = $reginfo[1];
         $product_id = $_GET['pid'];
         if (isset($region_id) && isset($sub_reg_id)) {
            if (isset($_GET['bndl_opt']) && isset($_GET['cust_opt'])) {
               $_bundle_item_id = $_GET['bndl_opt'];
               $cust_opt_id = $_GET['cust_opt'];
            }
         }
      }
   } else {

      if (isset($_GET['bndl_opt']) && isset($_GET['cust_opt'])) {
         $_bundle_item_id = $_GET['bndl_opt'];
         $cust_opt_id = $_GET['cust_opt'];
      }
      $product_id = $_GET['pid'];
      $region_id = Mage::getModel('core/cookie')->get('region');
      $sub_reg_id = Mage::getModel('core/cookie')->get('sub_region');
   }
}
if ($_GET['exist'] == 'no') {

   $product_id = $_GET['pid'];
   $region_id = $_POST['region_name'];
   $sub_reg_id = $_POST['sub_reg'];
   Mage::getModel('core/cookie')->set('region', $region_id, 0, '/');
   Mage::getModel('core/cookie')->set('sub_region', $sub_reg_id, 0, '/');
   if (isset($_POST['opt_id1']) && isset($_POST['cust_opt_id1'])) {
      $_bundle_item_id = $_POST['opt_id1'];
      $cust_opt_id = $_POST['cust_opt_id1'];
   }
   if (isset($_POST['opt_id2']) && isset($_POST['cust_opt_id2'])) {
      $_bundle_item_id = $_POST['opt_id2'];
      $cust_opt_id = $_POST['cust_opt_id2'];
   }
   if (isset($_POST['opt_id3']) && isset($_POST['cust_opt_id3'])) {
      $_bundle_item_id = $_POST['opt_id3'];
      $cust_opt_id = $_POST['cust_opt_id3'];
   }
   if (isset($_POST['opt_id4']) && isset($_POST['cust_opt_id4'])) {
      $_bundle_item_id = $_POST['opt_id4'];
      $cust_opt_id = $_POST['cust_opt_id4'];
   }
   if ($customerId != "") {
      $user_reg_info = getUsersInfo($customerId);
      if ($user_reg_info == 'false') {
         saveUserInfo($customerId, $region_id, $sub_reg_id);
      }
   }
}

$script = "";
if (isset($region_id)) {

   $resource = Mage::getSingleton('core/resource');
   $readConnection = $resource->getConnection('core_read');
   $query = 'SELECT * FROM diji_salesregions_delivery_days where region_id=' . $region_id;
   $results = $readConnection->fetchAll($query);
   $script = "";
   $alter_date = "";
   $excl_date = "";
   foreach ($results as $avldays) {
      //$time = date("H:i:s", Mage::getModel('core/date')->timestamp(time()));
      //if ($time >= $avldays['delivery_timestart'] && $time <= $avldays['delivery_timeend']) {
         $script .= " date.getDay() !=" . ($avldays['weekday'] - 1) . " &&";
     // } else {
        // $script = "date.getDay()!=9";
      //}
   }
   $query_alternate = 'SELECT  alternate_date,excluded_date,delivery_timestart FROM diji_salesregions_excluded_dates where region_id=' . $region_id;
   $results_alternate = $readConnection->fetchAll($query_alternate);

   foreach ($results_alternate as $alternate_day) {
     // $time = date("H:i:s", Mage::getModel('core/date')->timestamp(time()));
     // if ($time >= $alternate_day['delivery_timestart']) {
			if($alternate_day["alternate_date"] !='NULL' && !empty($alternate_day["alternate_date"])){
			
         $alter_date .= '"' . date("j-n-Y", strtotime($alternate_day["alternate_date"])) . '",';
		 }
		 if($alternate_day["excluded_date"] !='NULL' && !empty($alternate_day["excluded_date"])){
         $excl_date.= '"' . date("j-n-Y", strtotime($alternate_day["excluded_date"])) . '",';
		 }
    //  } else {
      //   $alter_date = "n"; //only for date out of range of calender 
     //    $excl_date = "n"; //only for date out of range of calender 
     // }
   }
 $lead_day_query = 'SELECT   lead_days FROM diji_salesregions_delivery_days where region_id=' . $region_id;
   $results_lead_day = $readConnection->fetchAll($lead_day_query);
if($results_lead_day){ 
   foreach ($results_lead_day as $lead_days) {
	$lead_day=$lead_days['lead_days'];
	}
   
   }
   else{
   $lead_day="0";
   
   }
   
   echo rtrim($alter_date, ',') . "^" . rtrim($excl_date, ',') . "^" . rtrim($script, '&&') . "^" . $_bundle_item_id . "^" . $cust_opt_id . "^";
}
?> 
<script>
   function showPop(url,id,type,qty_to_insert,opt_id,cust_opt_id){
      
      var j=jQuery.noConflict();
      j("#"+opt_id).attr("checked", "checked");
      j("#pop"+id).show();
      j("#overlay").css("height",getDocHeight());
      j("#overlay").show();

      j("#datepicker"+id).datepicker({
       dateFormat: 'dd MM yy',
		  minDate:'<?php echo $lead_day;?>d',  
         beforeShowDay:unavailable,
        
         onSelect: function (dateText, inst) {
            var selectedDate = new Date(dateText);
            var val=document.getElementById('datepicker'+id).value;
            var cust_opt_txt='options_'+cust_opt_id+'_text';
            document.getElementById(cust_opt_txt).value=val;
            sendcart1(url, id, type, qty_to_insert);
            document.getElementById('pop'+id).style.display="none";
            document.getElementById('overlay').style.display="none";

         }
      });  

   }


   function unavailable(date) {
      var alter_dates = [<?php echo rtrim($alter_date, ',') ?>];
      var excl_dates=[<?php echo rtrim($excl_date, ',') ?>];
      var j=jQuery.noConflict();
      dmy = date.getDate() + "-" + (date.getMonth() + 1) + "-" + date.getFullYear();

      if((<?php echo rtrim($script, '&&'); ?>)&&(j.inArray(dmy, alter_dates) == -1 )){
	
         return [false];
      }
      else if (j.inArray(dmy, excl_dates) == -1 ){
         return [true];
      }
      else{
         return [false];
      }

   } 
</script>
<?php

function getUsersInfo($cust_id) {
   $read = Mage::getSingleton('core/resource')->getConnection('core_read');
   $readQuery = 'SELECT  * from  diji_customer_region_info where customer_id=' . $cust_id;
   $results = $read->fetchAll($readQuery);
   if ($results) {
      foreach ($results as $res) {
         $region_id = $res['regions_id'];
         $sub_region_id = $res['sub_regions_id'];
      }
      return $region_id . "," . $sub_region_id;
   }
   echo "false";
}

function saveUserInfo($cust_id, $region_id, $subregion_id) {

   $write = Mage::getSingleton("core/resource")->getConnection("core_write");
   $read1 = Mage::getSingleton('core/resource')->getConnection('core_read');
   $readQuery1 = 'SELECT  * from diji_customer_region_info where customer_id=' . $cust_id;
   $results = $read1->fetchAll($readQuery1);
   if (count($results) > 0) {
      $upquery = "UPDATE  diji_customer_region_info SET regions_id=$region_id,sub_regions_id=$subregion_id WHERE customer_id=$cust_id";
      $write->query($upquery);
   } else {
      $query = "INSERT INTO diji_customer_region_info (id,regions_id,sub_regions_id,customer_id) VALUES ('',$region_id,$subregion_id,$cust_id)";
      $write->query($query);
   }
}
?>
   
