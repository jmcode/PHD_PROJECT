<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class('post'); ?>>
	
	<?php 
		$ft_image_atts = array( 'fallback_to_first_attached' => false, 'linkto' => 'none' );
		echo cudazi_featured_image( $ft_image_atts ); 

	?>	
	
	<div class="text-holder">
		
		<header class="entry-header">							
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'cudazi' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>											
		</header>
		
		<div class="entry-content">
			<?php the_content( __( 'Read More...', 'cudazi' ) ); ?>
			<?php wp_link_pages( array( 'before' => '' . __( '<p>Pages:', 'cudazi' ), 'after' => '</p>' ) ); ?>
			<?php edit_post_link( __( 'Edit', 'cudazi' ), '<p>', '</p>' ); ?>
		</div><!--//entry-content-->
		
	</div><!--//text-holder-->
	
</article>
<?php comments_template( '', true ); ?>
<?php endwhile; ?>
