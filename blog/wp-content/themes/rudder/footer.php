<?php 
/*
* The template for displaying the footer.
*/
?>

		</div><!-- //main -->
		
		<!-- footer -->
		<footer id="footer">
			
			<?php if ( is_active_sidebar( 'footer-bottom' ) ) { ?>
					<?php dynamic_sidebar( 'footer-bottom' ); ?>
			<?php }else{ 
				echo sprintf( 
						"<p>" . "© 2013 Copyright <a href='http://www.phdcleanse.com' style='color:#510055'>PHD Cleanse</a>" . "</p>", 
						date("Y"), 
						CUDAZI_THEME_NAME, 
						"<a href='http://cudazi.com/'>Cudazi</a>" 
					); 
				} ?>
			
			<a href="#" id="scroll-top" class="right" title='<?php _e('Scroll to top','cudazi'); ?>'>&uarr;</a>
		</footer>
		
	</div><!-- //wrapper -->

	<?php wp_footer(); ?>
</body>
</html>