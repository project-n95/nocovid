<?php
/**
 *    Kalium WordPress Theme
 *
 *    Laborator.co
 *    www.laborator.co
 *
 * @deprecated 3.0 This template file will be removed or replaced with new one in templates/ folder.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

global $wp_query;

// Total found posts
$found_posts = $wp_query->found_posts;

// Meta
$search_thumbnails = kalium_get_theme_option( 'search_thumbnails' );
$show_add_to_cart = kalium_get_theme_option( 'shop_search_add_to_cart' );

// Search link
$search_link = '<a href="#" class="change-search-keyword" title="' . esc_html__( 'Click to change your search', 'kalium' ) . '" data-search-url="' . esc_attr( get_search_link( '%s' ) ) . '">' . get_search_query( true ) . '</a>';
?>
<div class="container">

    <div class="section-title">

        <h1><?php printf( esc_html__( '%d results for “%s”', 'kalium' ), $found_posts, $search_link ); ?></h1>

		<?php if ( $found_posts ) : ?>
            <p><?php printf( _n( 'We have found one match with the word you searched.', 'We have found %d results with the word you searched.', $found_posts, 'kalium' ), $found_posts ); ?></p>
		<?php else : ?>
            <p><?php _e( 'There is nothing found that matches your search criteria.', 'kalium' ); ?></p>
		<?php endif; ?>

    </div>

    <div class="page-container">

        <div class="search-results-holder">

			<?php
			if ( have_posts() ) :
				while ( have_posts() ) : the_post();
					global $post;

					$is_product = 'product' === $post->post_type;
					?>

                    <div class="result-box">

						<?php if ( $search_thumbnails && has_post_thumbnail() ) : ?>
                            <div class="result-image">
                                <a href="<?php the_permalink(); ?>">
									<?php
									echo kalium_get_attachment_image( get_post_thumbnail_id(), apply_filters( 'kalium_search_thumbnail_size', array(
										220,
										220,
									) ) ); ?>
                                </a>
                            </div>
						<?php endif; ?>

                        <div class="result-info">
                            <h3>
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>

                            <p><?php echo strip_shortcodes( get_the_excerpt() ); ?></p>

	                        <?php
                                // Add to cart link
                                if ( $is_product && $show_add_to_cart ) {
	                                woocommerce_template_loop_add_to_cart();
                                }
	                        ?>

                            <a href="<?php the_permalink(); ?>" class="post-link"><?php printf( esc_html__( 'Continue %s', 'kalium' ), '<i class="flaticon-arrow413"></i>' ); ?></a>

                        </div>
                    </div>

				<?php

				endwhile;


				?>
                <div class="pagination-container align-center">

					<?php
					echo paginate_links( apply_filters( 'kalium_search_pagination_args', array(
						'mid_size'  => 4,
						'end_size'  => 1,
						'total'     => $wp_query->max_num_pages,
						'prev_text' => sprintf( '%1$s %2$s', '<i class="flaticon-arrow427"></i>', esc_html__( 'Previous', 'kalium' ) ),
						'next_text' => sprintf( '%2$s %1$s', '<i class="flaticon-arrow413"></i>', esc_html__( 'Next', 'kalium' ) ),
					) ) );
					?>

                </div>

			<?php

			endif;
			?>
        </div>

    </div>

</div>