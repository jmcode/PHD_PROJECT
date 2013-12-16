<article id="post-<?php the_ID(); ?>" <?php post_class('post'); ?>>												

	<?php 		
		$ft_image_atts = array( 
			'fallback_to_first_attached' => false, 
			'hide_on_single' => cudazi_get_option( 'ft_hide_on_single', false ),
			'linkto' => cudazi_get_option( 'ft_img_linkto', 'post' )  /* Use: file, post or none */
		);
		echo cudazi_featured_image( $ft_image_atts ); 
	?>	
	
	<div class="text-holder">
	
		<header class="entry-header">	
			<span class="date"><?php the_time( 'M d' ); ?><em class="year"><?php the_time( 'Y' ); ?></em></span>			
			<?php get_template_part( 'meta', 'post' ); ?>
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'cudazi' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>											
		</header>
	
		<div class="entry-content">
			<?php the_content( __( 'Read More...', 'cudazi' ) ); ?>
			<?php if ( is_single() ) { wp_link_pages( array( 'before' => '' . __( '<p>Pages:', 'cudazi' ), 'after' => '</p>' ) ); } ?>
		</div><!--//entry-content-->
						
								
	</div><!--//text-holder-->
</article><!--//post--> 
