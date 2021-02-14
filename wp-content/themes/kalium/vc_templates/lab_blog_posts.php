<?php
/**
 *    Blog Posts
 *
 *    Laborator.co
 *    www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Enqueue isotope
kalium_enqueue_isotope_and_packery_library();

// Atts
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );

extract( $atts );

list( $query_args, $blog_query ) = vc_build_loop_query( $blog_query );

$unique_id            = 'blogposts-' . mt_rand( 1000, 10000 );
$is_borderless_layout = false !== strpos( $layout, 'borderless' );

if ( $is_borderless_layout ) {
	$layout = str_replace( '-borderless', '', $layout );
}

if ( function_exists( 'uniqid' ) ) {
	$unique_id .= uniqid();
}

$is_masonry    = false;
$columns_class = '';
$columns_count = intval( $columns );

$more_link = vc_build_link( $more_link );

$blog_posts_options = explode( ',', $blog_posts_options );

$thumbnail_hover_effect = kalium_get_theme_option( 'blog_thumbnail_hover_effect' );

switch ( $masonry ) {

	case 'masonry':
	case 'packery':
	case 'fitRows':
		$is_masonry = true;
		break;
}

switch ( $columns ) {

	case '1':
		$columns_class = 'col-xs-12 col-sm-12';
		break;

	case '2':
		$columns_class = 'col-xs-12 col-sm-6';
		break;

	case '4':
		$columns_class = 'col-xs-12 col-md-3 col-sm-6';
		break;

	default:
		$columns_class = 'col-xs-12 col-md-4 col-sm-6';
		$columns_count = 3;
}

// Custom Class
$css_classes = array(
	$this->getExtraClass( $el_class ),
	'row',
	'lab-blog-posts',
	'cols-' . $columns,
	'posts-layout-' . $layout,
	vc_shortcode_custom_css_class( $css ),
);

if ( $is_masonry ) {
	$css_classes[] = 'display-loading';
}

if ( $is_borderless_layout ) {
    $css_classes[] = 'borderless';
}

// Deprecated
if ( in_array( 'animated-eye-hover', $blog_posts_options ) ) {
	$css_classes[] = 'animated-eye-hover';
}
// End: Deprecated

if ( isset( $hover_layer_icon ) && 'animated-eye' == $hover_layer_icon ) {
	$css_classes[] = 'animated-eye-hover';
}

// Custom Hover Layer Icon
if ( isset( $hover_layer_icon ) && 'custom' == $hover_layer_icon ) {
	$attachment_attrs = [];

	$blog_post_hover_layer_icon_custom       = kalium_get_theme_option( 'blog_post_hover_layer_icon_custom' );
	$blog_post_hover_layer_icon_custom_width = kalium_get_theme_option( 'blog_post_hover_layer_icon_custom_width' );

	if ( is_numeric( $blog_post_hover_layer_icon_custom_width ) ) {
		$attachment_attrs['style'] = "width:{$blog_post_hover_layer_icon_custom_width}px";
	}

	$blog_post_hover_layer_icon_custom_markup = '<span class="custom-hover-icon">' . wp_get_attachment_image( $blog_post_hover_layer_icon_custom, 'original', false, $attachment_attrs ) . '</span>';

}

$css_class = preg_replace( '/\s+/', ' ', apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, implode( ' ', array_filter( $css_classes ) ), $this->settings['base'], $atts ) );

$image_size = empty( $image_size ) ? '400x250' : $image_size;

if ( $layout == 'left' ) {
	add_filter( 'excerpt_length', 'laborator_supershort_excerpt_length', 150 );

	$image_column_size = intval( $image_column_size );

	if ( $image_column_size ) {
		kalium_append_custom_css( "#{$unique_id} .blog-post-image", "flex: 0 0 {$image_column_size}%" );
	}
}
?>
    <div id="<?php echo $unique_id; ?>"
         class="<?php echo $css_class; ?>" <?php if ( $is_masonry ) : ?> data-masonry-mode="<?php echo $masonry; ?>"<?php endif; ?>>

		<?php
		if ( $blog_query->have_posts() ) :

			while ( $blog_query->have_posts() ) : $blog_query->the_post();

				echo '<div class="blog-post-column">';

				?>
                <div <?php post_class( 'blog-post-entry' ); ?>>
					<?php

					$post_thumb_id = get_post_thumbnail_id();

					if ( $post_thumb_id ) {
						$image = wpb_getImageBySize( array(
							'attach_id'  => $post_thumb_id,
							'thumb_size' => $image_size,
							'class'      => 'img-responsive',
						) );

						if ( ! empty( $image['thumbnail'] ) ) {

							?>
                            <div class="blog-post-image">
                                <a href="<?php the_permalink(); ?>" target="<?php echo esc_attr( kalium_blog_post_link_target() ); ?>" rel="bookmark">

									<?php echo kalium_image_placeholder_wrap_element( $image['thumbnail'] ); ?>

                                    <span class="hover-display<?php
									when_match( in_array( $thumbnail_hover_effect, array(
										'distanced',
										'distanced-no-opacity',
									) ), 'with-spacing' );
									when_match( in_array( $thumbnail_hover_effect, array(
										'full-cover-no-opacity',
										'distanced-no-opacity',
									) ), 'no-opacity' );
									?>">
									<?php if ( 'custom' == $hover_layer_icon ) : ?>
										<?php echo $blog_post_hover_layer_icon_custom_markup; ?>
									<?php else: ?>
                                        <i class="icon-basic-link"></i>
									<?php endif; ?>
								</span>
                                </a>
                            </div>
							<?php
						}
					}

					?>
                    <div class="blog-post-content-container">

						<?php if ( in_array( 'date', $blog_posts_options ) ) : ?>
                            <div class="blog-post-date">
								<?php the_time( get_option( 'date_format' ) ); ?>
                            </div>
						<?php endif; ?>

                        <h3 class="blog-post-title">
                            <a href="<?php the_permalink(); ?>" target="<?php echo esc_attr( kalium_blog_post_link_target() ); ?>" rel="bookmark"><?php the_title(); ?></a>
                        </h3>

						<?php if ( in_array( 'excerpt', $blog_posts_options ) ) : ?>
                            <div class="blog-post-excerpt">
								<?php the_excerpt(); ?>
                            </div>
						<?php endif; ?>

                        <?php
                            do_action( 'lab_blog_posts_content_after' );
                        ?>

                    </div>
					<?php

					?>
                </div>
				<?php

				echo '</div>';

			endwhile;

			wp_reset_postdata();

		endif;
		?>

    </div>


<?php if ( $more_link['url'] && $more_link['title'] ) : ?>
    <div class="more-link <?php echo isset( $show_effect ) && $show_effect ? $show_effect : ''; ?>">
        <div class="show-more">
            <div class="reveal-button">
                <a href="<?php echo esc_url( $more_link['url'] ); ?>"
                   target="<?php echo esc_attr( $more_link['target'] ); ?>" class="btn btn-white">
					<?php echo esc_html( $more_link['title'] ); ?>
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php

if ( $is_masonry ) {
	?>
    <div class="blog-posts-loading-message">
		<?php _e( 'Loading blog posts...', 'kalium' ); ?>
    </div>
	<?php
}

if ( 'left' == $layout ) {
	remove_filter( 'excerpt_length', 'laborator_supershort_excerpt_length', 150 );
}
