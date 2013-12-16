<?php


add_action( 'admin_init', 'theme_options_init' );
add_action( 'admin_menu', 'theme_options_add_page' );


// Init plugin options to white list our options
function theme_options_init(){
	register_setting( 'cudazi_options', 'cudazi_theme_options', 'theme_options_validate' );
}


// Load up the menu page
function theme_options_add_page() {
	add_theme_page( __( 'Theme Options', 'cudazi' ), __( 'Theme Options', 'cudazi' ), 'edit_theme_options', 'theme_options', 'theme_options_do_page' );
}




// Settings array()
$select_ft_img_linkto = array(
	'file' => array(
		'value' =>	'file',
		'label' => __( 'Image File', 'cudazi' )
	),
	'post' => array(
		'value' =>	'post',
		'label' => __( 'Post', 'cudazi' )
	),
	'none' => array(
		'value' =>	'none',
		'label' => __( 'No Link', 'cudazi' )
	)
);
$select_post_meta_cats_tags = array(
	'tags' => array(
		'value' =>	'tags',
		'label' => __( 'Display Tags', 'cudazi' )
	),
	'categories' => array(
		'value' =>	'categories',
		'label' => __( 'Display Categories', 'cudazi' )
	),
	'hide' => array(
		'value' =>	'hide',
		'label' => __( 'Display Nothing', 'cudazi' )
	)
);



/**
 * Create the options page
 */
function theme_options_do_page() {
	global $select_ft_img_linkto, $select_post_meta_cats_tags;

	if ( ! isset( $_REQUEST['settings-updated'] ) )
		$_REQUEST['settings-updated'] = false;

	?>
	<div class="wrap">	
		<?php screen_icon(); echo "<h2>" . CUDAZI_THEME_NAME . __( ' Theme Options', 'cudazi' ) . "</h2>"; ?>
		<?php if ( false !== $_REQUEST['settings-updated'] ) : ?><div class="updated fade"><p><strong><?php _e( 'Options saved', 'cudazi' ); ?></strong></p></div><?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'cudazi_options' ); ?>
			<?php $options = get_option( 'cudazi_theme_options' ); ?>			
			
			<p><?php echo sprintf( __('Custom settings and options for the %s theme. Be sure to click the save options button after making any changes.','cudazi'), CUDAZI_THEME_NAME ); ?></p>
			
			<h3><br /><?php _e('Header Settings and Style','cudazi'); ?></h3>
			
			<table class="form-table">
				

				<?php
				 $field_key = 'fixed_header';
				 if ( ! isset( $options[$field_key] ) )
				 	$options[$field_key] = '';
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Fixed Header', 'cudazi' ); ?></th>
					<td>
						<label class="description">
							<input id="<?php echo $field_key; ?>" name="cudazi_theme_options[<?php echo $field_key; ?>]" type="checkbox" value="1" <?php checked( '1', $options[$field_key] ); ?> /> <?php _e( 'Make the header fixed at the top of the page', 'cudazi' ); ?>
						</label>
					</td>
				</tr>




				<?php
				 $field_key = 'logo_url';
				 if ( ! isset( $options[$field_key] ) )
				 	$options[$field_key] = '';
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Full Logo URL', 'cudazi' ); ?></th>
					<td>
						<input id="<?php echo $field_key; ?>" class="regular-text" type="text" name="cudazi_theme_options[<?php echo $field_key; ?>]" value="<?php echo esc_attr( $options[$field_key] ); ?>" />
						<br />
						<span class="description"><?php _e( 'Enter the full URL to your custom logo.', 'cudazi' ); ?></span>
						<br />
						<span class="description"><?php _e( 'Tip: Upload it in the Media Library and then copy the full file URL. (Test the URL in your browser first)', 'cudazi' ); ?></span>
					</td>
				</tr>
				
				
				<?php
				 $field_key = 'disable_tagline';
				 if ( ! isset( $options[$field_key] ) )
				 	$options[$field_key] = '';
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Tagline / Description', 'cudazi' ); ?></th>
					<td>
						<label class="description">
							<input id="<?php echo $field_key; ?>" name="cudazi_theme_options[<?php echo $field_key; ?>]" type="checkbox" value="1" <?php checked( '1', $options[$field_key] ); ?> /> <?php _e( 'Hide the tagline / blog description to make more room for the logo / menu.', 'cudazi' ); ?>
						</label>
					</td>
				</tr>
				
				
				<?php
				 $field_key = 'post_meta_cats_tags';
				 if ( ! isset( $options[$field_key] ) )
				 	$options[$field_key] = '';
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Post Meta', 'cudazi' ); ?></th>
					<td>
						<select name="cudazi_theme_options[<?php echo $field_key; ?>]">
							<?php
								$selected = $options[$field_key];
								$p = '';
								$r = '';

								foreach ( $select_post_meta_cats_tags as $option ) {
									$label = $option['label'];
									if ( $selected == $option['value'] ) // Make default first in list
										$p = "\n\t<option style=\"padding-right: 10px;\" selected='selected' value='" . esc_attr( $option['value'] ) . "'>$label</option>";
									else
										$r .= "\n\t<option style=\"padding-right: 10px;\" value='" . esc_attr( $option['value'] ) . "'>$label</option>";
								}
								echo $p . $r;
							?>
						</select>
						<br />
						<span class="description"><?php _e( 'During post loop, show tags, categories or nothing.', 'cudazi' ); ?></span>
					</td>
				</tr>
				
				
				
				
				
				
				</table>
				
				
				
				<h3><br /><?php _e('General Settings','cudazi'); ?></h3>				

				<table class="form-table">
				

				<?php				
				 $field_key = 'color_primary';
				 if ( ! isset( $options[$field_key] ) )
				 	$options[$field_key] = '';
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Primary Color', 'cudazi' ); ?></th>
					<td>
						<input id="<?php echo $field_key; ?>" class="regular-text" type="text" name="cudazi_theme_options[<?php echo $field_key; ?>]" value="<?php echo ( $options[$field_key] ? esc_attr( $options[$field_key] ) : '#' ); ?>" style='width: 150px;' />
						<div id="picker"></div>
						<br />
						<span class="description"><?php _e( 'Enter the color code you want used as the primary color.<br />Leave blank for default color. For making extensive CSS edits, set up a child theme.', 'cudazi' ); ?></span>
					</td>
				</tr>
				<script>
					jQuery(function($){
						var picker = $('#picker');
						var input = $('#color_primary');
						$(picker).hide();
							$(picker).farbtastic(input);
							$(input).click(function(){
								$(picker).fadeIn();
						});					    
					});
				</script>
				
				
				
				
				
				<?php
					 $field_key = 'ft_hide_on_single';
					 if ( ! isset( $options[$field_key] ) )
					 	$options[$field_key] = '';
					?>
					<tr valign="top"><th scope="row"><?php _e( 'Featured Image', 'cudazi' ); ?></th>
						<td>
							<label class="description">
								<input id="<?php echo $field_key; ?>" name="cudazi_theme_options[<?php echo $field_key; ?>]" type="checkbox" value="1" <?php checked( '1', $options[$field_key] ); ?> /> <?php _e( 'Hide the featured image on a single post/portfolio page', 'cudazi' ); ?>
							</label>
						</td>
					</tr>
					
					
					
					
					
					<?php
				 $field_key = 'ft_img_linkto';
				 if ( ! isset( $options[$field_key] ) )
				 	$options[$field_key] = '';
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Featured Image Links To', 'cudazi' ); ?></th>
					<td>
						<select name="cudazi_theme_options[<?php echo $field_key; ?>]">
							<?php
								$selected = $options[$field_key];
								$p = '';
								$r = '';

								foreach ( $select_ft_img_linkto as $option ) {
									$label = $option['label'];									
									if ( $selected == $option['value'] ) // Make default first in list
										$p = "\n\t<option style=\"padding-right: 10px;\" selected='selected' value='" . esc_attr( $option['value'] ) . "'>$label</option>";
									else
										$r .= "\n\t<option style=\"padding-right: 10px;\" value='" . esc_attr( $option['value'] ) . "'>$label</option>";
								}
								echo $p . $r;
							?>
						</select>
						<br />
						<span class="description"><?php _e( 'If featured images are displayed in a post loop, choose where you want them to be linked to, or disable the link.', 'cudazi' ); ?></span>
					</td>
				</tr>
				
				
				
				
				
				
				</table>
				


				<h3><br /><?php _e('Other Settings / Information','cudazi'); ?></h3>

				
				
				<table class="form-table">
				
				
					
				
					
					<tr valign="top"><th scope="row"><?php _e( 'Background Color or Image', 'cudazi' ); ?></th>
						<td><span class='description'><?php _e('Set the background color or image in appearance > background.','cudazi'); ?></span></td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><?php _e( 'Custom Bio Sidebar', 'cudazi' ); ?></th>
						<td>
							<span class="description"><?php _e( 'All options (Photo, Text, Search) for the custom bio in the sidebar are found in the widget itself, go to Appearance > Widgets and drag it into the sidebar. To add social links, use the "Social Links" menu.', 'cudazi' ); ?></span>
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><?php _e( 'Menu Builder', 'cudazi' ); ?></th>
						<td>
							<span class="description"><?php _e( 'Drag and drop menus are set up in Appearance > Menus.', 'cudazi' ); ?></span>
						</td>
					</tr>
			
				
				
			</table>			
			<p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'cudazi' ); ?>" /></p>			
			<br />
		</form>
	</div>
	<?php
}



/**
 * Sanitize and validate input. Accepts an array, return a sanitized array.
 */
function theme_options_validate( $input ) {

	global $select_ft_img_linkto, $select_post_meta_cats_tags;
		
	// Our checkbox value is either 0 or 1
	if ( ! isset( $input['fixed_header'] ) )
		$input['fixed_header'] = null;
	$input['fixed_header'] = ( $input['fixed_header'] == 1 ? 1 : 0 );

	// Say our text option must be safe text with no HTML tags
	$input['logo_url'] = wp_filter_nohtml_kses( $input['logo_url'] );
	
	if ( $input['color_primary'] != '#' ) {
		$input['color_primary'] = wp_filter_nohtml_kses( $input['color_primary'] );
	}else{ $input['color_primary'] = null; }
	
	// Our checkbox value is either 0 or 1
	if ( ! isset( $input['disable_tagline'] ) )
		$input['disable_tagline'] = null;
	$input['disable_tagline'] = ( $input['disable_tagline'] == 1 ? 1 : 0 );
	
	// Our checkbox value is either 0 or 1
	if ( ! isset( $input['ft_hide_on_single'] ) )
		$input['ft_hide_on_single'] = null;
	$input['ft_hide_on_single'] = ( $input['ft_hide_on_single'] == 1 ? 1 : 0 );
	
	// Our select option must actually be in our array of select options	
	if ( ! array_key_exists( $input['ft_img_linkto'], $select_ft_img_linkto ) ) 
		$input['ft_img_linkto'] = null;		
	
	// Our select option must actually be in our array of select options	
	if ( ! array_key_exists( $input['post_meta_cats_tags'], $select_post_meta_cats_tags ) )
		$input['post_meta_cats_tags'] = null;
	
	// return cleaned data
	return $input;
}
// the above was adapted from http://planetozh.com/blog/2009/05/handling-plugins-options-in-wordpress-28-with-register_setting/




// Function to grab custom settings
if ( ! function_exists( 'cudazi_get_option' ) ) {
	function cudazi_get_option( $key = '', $default = '' ) {
		
		// Key must be set, otherwise just return nothing
		if ( ! $key ) {
			return false;
		}
		
		// Options from wp options table
		$option_arr = get_option( 'cudazi_theme_options' );

		// If the option array is actually an array		
		// Example - before the first save to the databse
		if ( is_array( $option_arr ) ) {
		
			// Check if the options array contains this key
			if ( array_key_exists( $key, $option_arr ) ) {
			
				// If the array key has a value
				if ( $option_arr[$key] ) {
					return $option_arr[$key];
				}else{
					// Key is empty, return the default
					return $default;
				}				
			}else{			
				// key does not exist
				return $default;
			}	
		}else{		
			// Option array is not set
			return $default;
		}			
	} // end function
} // end if function exists



