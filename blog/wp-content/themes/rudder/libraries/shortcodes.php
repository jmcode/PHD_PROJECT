<?php





	// [cudazi_message type='warning' close='Hide This']Warning, you are about to catch on fire.[/cudazi_message]
	function cudazi_message_sc($atts, $content = null) {
		extract(shortcode_atts(array(
			'type' => 'success',
			'close' => __( 'Hide', 'cudazi' )
		), $atts));
		
		$output = "<p class='". $type . " message rounded clearfix'>";
		
		if( $close) { 
			$output .= "<a class='hideparent right' href='#'>" . $close . "</a>";
		}
		$output .= $content . "</p>";
		
		return $output;
	}
	add_shortcode('cudazi_message', 'cudazi_message_sc');





	//
	// 	Columns
	//
	//	[column_start width='one_half']
	//		...content...	
	//	[column_end]
	//	[column_start width='one_half last']
	//		...content...
	//	[column_end]
	if ( ! function_exists( 'column_start_sc' ) ) {
		function column_start_sc( $atts ) {	
			extract(shortcode_atts(array(
				'width' => 'one_half'
			), $atts));		
			return "<div class='sc_column ". $width ."'>";
		}
		add_shortcode('column_start', 'column_start_sc');
	}
	if ( ! function_exists( 'column_end_sc' ) ) {
		function column_end_sc($atts, $content = null) {	
			extract(shortcode_atts(array(
				/* no attributes at this time */
			), $atts));		
			return "</div>";
		}
		add_shortcode('column_end', 'column_end_sc');
	}
	if ( ! function_exists( 'column_clear_sc' ) ) {
		function column_clear_sc($atts, $content = null) {	
			extract(shortcode_atts(array(
				/* no attributes at this time */
			), $atts));		
			return "<div class='clear clearfix'></div>";
		}
		add_shortcode('column_clear', 'column_clear_sc');
	}





