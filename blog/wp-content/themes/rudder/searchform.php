<!-- Source: searchform.php -->
<?php	
	$placeholder_txt = __('Search this site...', 'cudazi'); 
	$existing_search_query = get_search_query();	
	if ( $existing_search_query ){
		$placeholder_txt = $existing_search_query;
	}	 
?>
<form action="<?php echo home_url( '/' ); ?>" method="get" class="search-form" id="searchform">
	<fieldset>
		<input name="s" id="s" type="text" placeholder="<?php echo $placeholder_txt; ?>" />
		<input type="submit" value="<?php _e('Go', 'cudazi'); ?>" id="searchsubmit" />
	</fieldset>
</form>
