
var $j = jQuery.noConflict(); 

$j(document).ready(function($){
	$("#primary-nav ul").supersubs({ 
            minWidth:    11,   // minimum width of sub-menus in em units 
            maxWidth:    27,   // maximum width of sub-menus in em units 
            extraWidth:  1     // extra width can ensure lines don't sometimes turn over 
                               // due to slight rounding differences and font-family 
        }).superfish({ 
            autoArrows:  false,                           // disable generation of arrow mark-up 
            dropShadows: false                            // disable drop shadows 
    }); 





      $('.flexslider').flexslider({
        animation: "fade",
        start: function(slider){
          $('body').removeClass('loading');
        }
		
	
      });
	  
/*********tab function***********/
if($(".tabs").length){
$(function() {

			var $tabs = $('.tabs').tabs();
	
			$(".ui-tabs-panel").each(function(i){
	
			  var totalSize = $(".ui-tabs-panel").size() - 1;
	
			  if (i != totalSize) {
			      next = i + 2;
		   		  $(this).append("<a href='#' class='next-tab mover' rel='" + next + "'>Next Step</a>");
			  }
	  
			  if (i != 0) {

			      prev = i;
		   		  $(this).append("<a href='#' class='prev-tab mover' rel='" + prev + "'>Back</a>");
			  }
   		
			});
	
			$('.next-tab, .prev-tab').click(function() { 
		           $tabs.tabs('select', $(this).attr("rel"));
		           return false;
		       });
       

		});	  
}
	  
	  
	  

  $(function(){
   $('#latest-facebok .footer-facebook').fbWall({ id:'403936502957056',accessToken:'137071036446715|pkGoEc3WvMOmv284itcouMd_1f4',showGuestEntries:true,showComments:false,max:3});
  });
  
  
 /*if($('#crosssell-products-list').length){
  $.fn.colorbox({width:"30%", inline:true, href:".crosssell"});
 

 }*/
  /*$(".inline").colorbox({inline:true, width:"50%"});
				
				//Example of preserving a JavaScript event for inline calls.
				$("#click").click(function(){ 
					$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
					return false;
				});
	*/
	  
	   });
