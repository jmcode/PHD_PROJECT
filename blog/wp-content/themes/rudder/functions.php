<?php
	
	
	
	
	// Load custom libraries used in theme
	$cudazi_libraries = 
		array( 
			'themesetup',
			'theme-options',			
			'debug',
			'filters',
			'shortcodes',
			'widget-areas',
			'featuredimages',
			'meta-boxes',
			'custom-post-types',
			'comments',			
			'plugins/cudazi-bio-widget'
		);
	foreach( $cudazi_libraries as $library ) {
		include_once( 'libraries/' . $library . '.php' );
	}
	
	

	
	// Theme and Version Information
	$cudazi_theme_data = wp_get_theme();	
	define('CUDAZI_THEME_NAME', $cudazi_theme_data->get( 'Name' ) );
	define('CUDAZI_THEME_AUTHOR', $cudazi_theme_data->get( 'Author' ) );
	define('CUDAZI_THEME_URI', $cudazi_theme_data->get( 'AuthorURI' ) );
	define('CUDAZI_THEME_VERSION', $cudazi_theme_data->get( 'Version' ) );
	define('CUDAZI_THEME_INFOLINE', CUDAZI_THEME_NAME . ' by ' . CUDAZI_THEME_AUTHOR . ' (' . CUDAZI_THEME_URI . ') v' . CUDAZI_THEME_VERSION);
	
	add_action('wp_footer','cudazi_display_themeinfo');
	if ( ! function_exists('cudazi_display_themeinfo' ) ) {
		function cudazi_display_themeinfo() {
			echo '<!-- ' . CUDAZI_THEME_INFOLINE . ' -->'; // Display for easier debugging remotely
		}
	}
	
	
	
	
	
	
	// Output via wp_head()
	add_action( 'wp_enqueue_scripts', 'cudazi_scripts');
	if ( ! function_exists( 'cudazi_scripts' ) ) {
		function cudazi_scripts() {
			
			// Register			
			wp_register_style('cudazi_style', get_bloginfo( 'stylesheet_url' ),false, CUDAZI_THEME_VERSION, 'all');
			
			// Load CSS			
			wp_enqueue_style('cudazi_style');
		
			// Register Scripts						
			wp_register_script("cudazi_plugins", get_template_directory_uri() . "/js/plugins.js", array('jquery'), CUDAZI_THEME_VERSION, false);
			wp_register_script("cudazi_general", get_template_directory_uri() . "/js/script.js", array('jquery'), CUDAZI_THEME_VERSION, false);
			
			// Load Scripts
			if ( is_singular() && get_option( 'thread_comments' ) )	{ wp_enqueue_script( 'comment-reply' ); }
			wp_enqueue_script("jquery");						
			wp_enqueue_script("cudazi_plugins");		
			wp_enqueue_script("cudazi_general");
		}
	}
	
	
	
	
	// Admin JavaScript
	add_action( 'admin_print_scripts', 'cudazi_scripts_admin');
	if ( ! function_exists( 'cudazi_scripts_admin' ) ) {
		function cudazi_scripts_admin() {
			wp_enqueue_script( 'farbtastic' );
		}
	}
	
	// Admin CSS
	add_action( 'admin_print_styles', 'cudazi_styles_admin');
	if ( ! function_exists( 'cudazi_styles_admin' ) ) {
		function cudazi_styles_admin() {
			wp_enqueue_style( 'farbtastic' );
		}
	}
	
	
	
	
	// Remove Default Contact Form 7 CSS
	function disable_contact7_scripts() {
		wp_deregister_script( 'contact-form-7' );
	}
	add_action( 'wp_print_scripts', 'disable_contact7_scripts' );
	function disable_contact7_styles() {
		wp_deregister_style( 'contact-form-7' );
		wp_deregister_style( 'contact-form-7-rtl' );
	}
	add_action( 'wp_print_styles', 'disable_contact7_styles' );

	
	
	
	// Featured Image Function
	if ( ! function_exists( 'cudazi_featured_image' ) ) {
		function cudazi_featured_image( $params = array() ) {
			
			$image = $image_full = "";
			global $post;
			// Defaults
			$fallback_to_first_attached = false;
			$thumbnail_size = 'standard';
			$full_size = 'full';
			$linkto = 'file';
			$hide_on_single = false;
			$img_w = $img_h = "";
			
			// Params, override defaults if set
			extract($params);
			
			if ( is_single() && $hide_on_single ) {
				// hidden on single post
				return false;
			}else{
					
				if ( has_post_thumbnail( $post->ID ) ) {
					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $thumbnail_size );
					//$img_w = $image[1]; // use width later
					//$img_h = $image[2]; // use height later
					$image = $image[0];
					$image_full = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $full_size );
					$image_full = $image_full[0];						
				}else {
					if ( $fallback_to_first_attached ) {					
						$args = array( 'post_type' => 'attachment', 'order' => 'ASC', 'orderby' => 'menu_order', 'post_mime_type' => 'image', 'numberposts' => 1, 'post_status' => null, 'post_parent' => $post->ID );
						$attachments = get_posts( $args );			
						if ($attachments) {
							foreach ( $attachments as $attachment ) {
								$image = wp_get_attachment_image_src( $attachment->ID, $thumbnail_size );
								$img_w = $image[1]; // use width later
								$img_h = $image[2]; // use height later
								$image = $image[0];								
								$image_full = wp_get_attachment_image_src( $attachment->ID, $full_size );
								$image_full = $image_full[0];
							} // foreach
						} // if attachments
					}
				}
				
				// Get Post URL if linking to post
				if ( $linkto == 'post' ) {
					$image_full = get_permalink();	
				}
				
				if ( $linkto == 'none' ) {
					$image_full = "#";
				}
				
				// Return HTML if image found
				if ( $image_full && $image ) { 
					$img_out_html = "<figure class='featured-image'>";
						if ( $linkto != 'none' ) { $img_out_html .= "<a href='". $image_full . "'>"; }
							$img_out_html .= "<img src='". $image . "' alt='" . the_title_attribute(array('echo'=>0)) . "' />";
						if ( $linkto != 'none' ) { $img_out_html .= "</a>"; }
					$img_out_html .= "</figure>";
					return $img_out_html;
				}else{ 
					return false; 
				}
			
			} // end if single / hide on single
			
		}
	}
	
	
	
	
	// Paginate_links() function wrapper
	if ( ! function_exists( 'cudazi_paginate' ) ) {
		function cudazi_paginate() {
			
			if ( get_option('permalink_structure') ) {
				global $wp_query, $wp_rewrite;
				$wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;
				$pagination = array(
					'base' => @add_query_arg('page','%#%'),
					'format' => '',
					'total' => $wp_query->max_num_pages,
					'current' => $current,
					'show_all' => false,
					'type' => 'plain',
					'prev_text'    => __('Previous','cudazi'),
					'next_text'    => __('Next','cudazi'),
				);
				if( $wp_rewrite->using_permalinks() ) $pagination['base'] = user_trailingslashit( trailingslashit( remove_query_arg( 's', get_pagenum_link( 1 ) ) ) . 'page/%#%/', 'paged' );
				if( !empty($wp_query->query_vars['s']) ) $pagination['add_args'] = array( 's' => get_query_var( 's' ) );
				
				$page_links = paginate_links( $pagination );
				if ( $page_links ) {			
					echo $page_links;		
				}				
			}else{
				posts_nav_link( ' - ', false, false );
			}			
		}
	}



	// Fallback (Pre 3.0) menu system
	if ( ! function_exists('cudazi_menu_fallback' ) ) {
		function cudazi_menu_fallback() {
			echo "<ul id='nav' class='menu'><li><a href='#'>" . __( 'Add a menu in Appearance, Menus', 'cudazi' ) . "</a></li></ul>";
		}
	}
		

	
	// Return an array of icons
	if ( ! function_exists( 'cudazi_get_icons' ) ) {
		function cudazi_get_icons($path = 'images/social_icons') {
			$icons = array();			
			if ($handle = @opendir( get_template_directory() . '/' . $path)) {
				while (false !== ($file = readdir($handle))) {
					if (preg_match("/^.*\.(jpg|jpeg|png|gif)$/i", $file)) {
						$icons[] = $file;						
					}					
				}
				closedir($handle);				
				return $icons;
			}
		}
	}


	
	
	// Return a radio button list of icons
	if ( ! function_exists( 'cudazi_icon_radiolist' ) ) {
		function cudazi_icon_radiolist($path = 'images/social_icons', $icon_array = array(), $selected_file = '' ) {			
			$list_of_icons = "";
			if ( $icon_array && is_array($icon_array) ) {
				foreach ( $icon_array as $icon) {					
					$checked = '';						
					if($icon == $selected_file){
						$checked = 'checked';
					}
					$list_of_icons .= "<label style='display:block; width: 50px; float: left;'><input type='radio' name='social_link_icon' value='".$icon."' " .$checked . " /><img src='".get_template_directory_uri() . "/" . $path . '/' . $icon . "' alt='".$icon."' style='padding-top: 10px; position: relative; top: 5px' /></label>";											
				}				
				return $list_of_icons . "<br clear='both' />";				
			} // is array			
		}
	}
	
	
	
	
	// Text or Image Logo
	if ( ! function_exists( 'cudazi_get_logo' ) ) {
		function cudazi_get_logo() {
			$logo = cudazi_get_option( 'logo_url', '' );
			if ( $logo ) {
				$logo = "<img src='" . esc_attr( $logo ) . "' alt='" . get_bloginfo( 'name' ) . "' />";
			}else{
				$logo = "<span class='textlogo'>" . get_bloginfo( 'name' ) . "</span>";
			}			
			return "<a href='http://www.phdcleanse.co.nz'>" . $logo . "</a>";
		}
	}
	
	
	
	// Alternate menu for small screens
	if ( ! function_exists( 'cudazi_alternate_menu' ) ) {
		function cudazi_alternate_menu( $args = array() ) {			
			
			if ( has_nav_menu( 'primary' ) ) {
			
				$output = '';
				
				// Defaults
				$menu_name = 'primary';
				$display = 'select';
				
				// Grab and apply args from function
				extract($args);						
				
				if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ $menu_name ] ) ) {
				
					$menu = wp_get_nav_menu_object( $locations[ $menu_name ] );						
					$menu_items = wp_get_nav_menu_items( $menu->term_id );				
					$output = "<select id='navigation-small'>";
					$output .= "<option value='' selected='selected'>" . __('Go to...', 'cudazi') . "</option>";
					foreach ( (array) $menu_items as $key => $menu_item ) {
					    $title = $menu_item->title;
					    $url = $menu_item->url;
						    
					    if ( $menu_item->menu_item_parent ) {
							$title = ' - ' . $title;
					    }
					    $output .= "<option value='" . $url . "'>" . $title . '</option>';
					}
					$output .= '</select>';
			    }
		
				return $output;							
			} // has menu
		}
	}
	
	
	// Fix or scroll the header, added directly to wp_head()
	if ( ! function_exists( 'cudazi_fixed_header' ) ) {
		function cudazi_fixed_header() {
			if ( cudazi_get_option( 'fixed_header', false ) == true ) { ?>
				<!-- Added via wp_head -->
				<style>
					#header { position: fixed; top: 0; left: 0; width: 100%; z-index: 50; }
					#wrapper { margin-top: 140px; }
					/*un-fix on small screens*/
					@media only screen and (max-width: 767px) {
						#header { position: relative;	}
						#wrapper { margin-top: 0; }
					}
				</style>
				<script>				
					/* <![CDATA[ */
						jQuery(function($) { 
							if ( ! $.browser.msie ) {
								$(window).scroll(function () { 		
									var scrollTop = $(document).scrollTop();		
									if ( scrollTop > 10 ) {
										$('#header').fadeTo(500, .95);	
									}else{		
										$('#header').fadeTo(500, 1);
									}
								});
							}
						});				
					/* ]]&gt; */
				</script>
				<!-- // Added via wp_head -->
				<?php
			}
		}
	}	
	add_action('wp_head','cudazi_fixed_header');
	
	
	// Dynamic CSS to add to wp_head()
	if ( ! function_exists( 'cudazi_dynamic_css' ) ) {
		function cudazi_dynamic_css() {
			$color_primary = cudazi_get_option( 'color_primary', false );
			if ( $color_primary ) { ?>
				<!-- Added via wp_head + theme settings -->
				<style>
					a {
						color: <?php echo $color_primary; ?>; 
					}					
				</style>
				<!-- // Added via wp_head + theme settings -->
				<?php
			}
		}
	}	
	add_action('wp_head','cudazi_dynamic_css');
	
	
	
		