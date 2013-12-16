<?php
/**
* Default Page Template
*/
get_header(); ?>

	<section id="content">
		<div class="content-holder">
			<?php get_template_part( 'content', 'page' ); ?>
		</div><!-- content-holder -->
	</section><!-- content -->
	
	<?php get_sidebar(); ?>

<?php get_footer(); ?>