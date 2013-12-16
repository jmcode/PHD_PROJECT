<?php
/**
* Template Name: Portfolio Grid
*/
get_header(); ?>
<section id="content" class="fullwidth portfolio-container">
	<div class="content-holder">
			
		
			<?php 			
				
				// Grab the standard page content above the portfolio items
				// Disable comments on this page
				global $post;
				$post->comment_status = "closed";						
				get_template_part( 'content', 'page' ); 
				
				// Get page variable from querystring
				$paged = 1;
				if ( get_query_var('paged') ) $paged = get_query_var('paged');
				if ( get_query_var('page') ) $paged = get_query_var('page');			
				
				// Portfolio Options array init, defaults in content-portfolio.php
				$portfolio_options = array();				
				
				// Get Custom Field Values to override on a per page basis
				$category_slugs_override = get_post_meta($post->ID, 'category_slugs', true);
				$tag_slugs_override = get_post_meta($post->ID, 'tag_slugs', true);				
				$posts_per_page_override = get_post_meta($post->ID, 'posts_per_page', true);
				
				// Query add on string
				$add_query = "";
				
				// Override category
				if ( $category_slugs_override ) {
					$portfolio_options['portfolio-category'] = $category_slugs_override;
					$add_query .= "&portfolio-category=" . $category_slugs_override;
				}

				// Override tags
				if ( $tag_slugs_override ) {
					$portfolio_options['portfolio-tags'] = $tag_slugs_override;
					$add_query .= "&portfolio-tags=" . $tag_slugs_override;
				}
				
				// Posts per page override
				if ( $posts_per_page_override ) {
					$add_query .= "&posts_per_page=" . $posts_per_page_override;
				}else{
					$add_query .= "&posts_per_page=9";
				}
				
				// Run Modified Query
				query_posts( $add_query . '&post_type=portfolio&paged=' . $paged );
				
				// Check if template part is loaded from another area or standalone
				$from_page_template = true;
				
				// Load the portfolio posts
				get_template_part( 'archive', 'portfolio' );
				
			?>		

	</div><!-- content-holder -->
</section><!-- content -->

<?php get_footer(); ?>