jQuery.noConflict();
jQuery(function($) { 

	
	/* Alternate Menu, on change of dropdown go to url
	___________________________________________________________________ */	
	$("#navigation-small").change(function() {
		window.location = $(this).find("option:selected").val();
	});




	/* Tipsy Tooltip Setup
	___________________________________________________________________ */	
	$('.tooltip, .flickr_badge_image img').tipsy({gravity: $.fn.tipsy.autoNS, opacity: 1});	
	$('ul.social-networks a').tipsy({gravity: 's', opacity: .75, offset: 2});
	$('.image-caption a').tipsy({gravity: 'w', opacity: 1});
	$('a#scroll-top').tipsy({ gravity: 'se' });


	
	
	/* Scroll to top animation
	___________________________________________________________________ */
	$('#scroll-top').click(function(){ 
		$('html, body').animate({scrollTop:0}, 600); return false; 
	});
	
	
	
	/* Hide Parent Elem
	___________________________________________________________________ */
	$('.hideparent').click(function(){ 
		$(this).parent().fadeOut(); 
		return false; 
	});
	
	
	
	
	/* Portfolio Filter	
	___________________________________________________________________ */
	$('.portfolio-filter a').click(function(){ 		
		// remove current on all
		$('.portfolio-filter a').removeClass('current');		
		var portfolio_wrap = $('.portfolio-item-wrap');
		var tag = $(this).attr('class');		
		// add current on current filter
		$(this).addClass('current');		
		if(tag === 'tag-all') {  
            $(portfolio_wrap).find('article').stop().fadeTo(500, 1);  
        } else {  
            $(portfolio_wrap).find('article').each(function() {  
                if(!$(this).hasClass(tag)) {  
                    $(this).stop().fadeTo(500, .2);
                } else {  
                    $(this).stop().fadeTo(500, 1);  
                }  
            });  
        }  		
		return false;
	});
	
	
	


}); // end jQuery dom ready