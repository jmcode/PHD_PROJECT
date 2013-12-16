<em class="meta">
<?php if ( is_single() ) { ?><span class="post-author"><?php _e('By:','cudazi'); ?> <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author"><?php the_author() ; ?></a></span><?php } ?>
<?php 
	//
	// Show tags, categories or nothing
	//
	$post_meta_cats_tags = cudazi_get_option( 'post_meta_cats_tags', 'tags' );
	if ( $post_meta_cats_tags == 'tags' ) { ?>
		<?php if ( get_the_tags() ) { ?>
			<?php _e('Tagged with', 'cudazi'); ?> <?php the_tags('',', ',''); ?> 
			<?php if ( comments_open() ) { ?> &mdash; <?php comments_popup_link( __( '0 Responses', 'cudazi' ), __( '1 Response', 'cudazi' ), __( '% Responses', 'cudazi' ), null, '' ); ?><?php } ?>
		<?php }else{ ?>
			<?php if ( comments_open() ) { ?><?php comments_popup_link( __( '0 Responses', 'cudazi' ), __( '1 Response', 'cudazi' ), __( '% Responses', 'cudazi' ), null, '' ); ?><?php } ?>
		<?php } ?>		
	<?php }else if( $post_meta_cats_tags == 'categories' ) { ?>
		<?php _e('Posted in', 'cudazi'); ?> <?php the_category(', '); ?>
		<?php if ( comments_open() ) { ?> &mdash; <?php comments_popup_link( __( '0 Responses', 'cudazi' ), __( '1 Response', 'cudazi' ), __( '% Responses', 'cudazi' ), null, '' ); ?><?php } ?>
	<?php }else if ( $post_meta_cats_tags == 'hide' ) { /* display nothing */ } ?>				
	
	<?php edit_post_link( __('[edit]','cudazi'), ''); ?>
</em>
