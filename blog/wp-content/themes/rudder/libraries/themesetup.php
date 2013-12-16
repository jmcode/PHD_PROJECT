<?php
	/** Tell WordPress to run cudazi_setup() when the 'after_setup_theme' hook is run. */
	add_action( 'after_setup_theme', 'cudazi_setup_theme' );
	
	if ( ! function_exists('cudazi_setup_theme' ) ) {
		function cudazi_setup_theme() {
	  
			
			// Add default posts and comments RSS feed links to head
			add_theme_support( 'automatic-feed-links' );
			
			
			// Adding support for structured-post-formats
			// Disables the default filtering into the_content
			/*
			add_theme_support( 'structured-post-formats',
				array( 
					'aside', 
					'gallery', 
					'link', 
					'image', 
					'quote', 
					'status', 
					'video', 
					'audio', 
					'chat' 
				)
			 );
			*/
			
		
			// Custom background WP function (+ set default background)
			$cb_defaults = array( 'default-image' => get_template_directory_uri() . '/images/background.png' );
			add_theme_support( 'custom-background', $cb_defaults );
			
			// Make theme available for translation
			// Translations can be filed in the /languages/ directory
			load_theme_textdomain( 'cudazi', get_template_directory() . '/languages' );
			$locale = get_locale();
			$locale_file = get_template_directory() . "/languages/$locale.php";
			if ( is_readable( $locale_file ) )
				require_once( $locale_file );
		
			// This theme uses wp_nav_menu() in one location.
			register_nav_menus( array(
				'primary' => __( 'Primary Menu', 'cudazi' )
			) );
			
			
			// Content Width
			if ( ! isset( $content_width ) ) $content_width = 1141;
			
			
			// Editor CSS
			// add_editor_style('style.css');
			
		}
	}
?>