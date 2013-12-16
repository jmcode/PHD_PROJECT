<?php
/**
* The Template for displaying single posts, customized to pull in a unique post template on a per-post basis.
*/
	get_header(); 
?>	

	<section id="content">
		<div class="content-holder">
			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>    				
				<?php get_template_part( 'content', get_post_format() ); ?>					
				<?php comments_template( '', true ); ?>								
			<?php endwhile; // end of the loop. ?>
		</div><!-- content-holder -->
	</section><!-- content -->
	
	<?php get_sidebar(); ?>
	
<?php get_footer(); ?>