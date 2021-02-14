<?php
/**
 * Kalium WordPress Theme
 *
 * Blog template functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Blog page heading title and description.
 */
if ( ! function_exists( 'kalium_blog_page_header' ) ) {

	function kalium_blog_page_header() {

		// Args
		$args = [];

		if ( kalium_blog_get_option( 'loop/header/show' ) ) {
			$args['heading_tag'] = 'h1';

			$queried_object = get_queried_object();
			$title          = kalium_blog_get_option( 'loop/header/title' );
			$description    = kalium_blog_get_option( 'loop/header/description' );

			// Description from post content
			if ( is_home() && ! empty( $queried_object->post_content ) ) {
				$description = apply_filters( 'the_content', $queried_object->post_content );
			}

			// Category, tag and author pages show custom title
			if ( apply_filters( 'kalium_blog_page_heading_replace_for_taxonomies', true ) ) {
				$separator = apply_filters( 'kalium_blog_page_heading_replace_for_taxonomies_separator', ' / ' );
				$parents   = apply_filters( 'kalium_blog_page_heading_replace_for_taxonomies_parents', 'multiple' );

				// Category
				if ( is_category() ) {
					if ( apply_filters( 'kalium_blog_page_header_last_category_title_only', false ) ) {
						$categories = single_cat_title( $separator, false );
					} else {
						$categories = strip_tags( get_the_category_list( $separator, $parents ) );
					}

					$title       = sprintf( '%s %s <span>%s</span>', esc_html__( 'Category', 'kalium' ), $separator, $categories );
					$description = category_description( $queried_object->object_id );
				} // Tag
				else if ( is_tag() ) {
					$tag         = single_term_title( '', false );
					$title       = sprintf( '%s %s <span>%s</span>', esc_html__( 'Tag', 'kalium' ), $separator, $tag );
					$description = tag_description( $queried_object->object_id );
				} // Author
				else if ( is_author() ) {
					$author       = get_user_by( 'id', get_queried_object_id() );
					$display_name = $author instanceof WP_User ? $author->display_name : get_the_author();
					$title        = sprintf( '%s %s <span>%s</span>', esc_html__( 'Author', 'kalium' ), $separator, $display_name );
					$description  = get_the_author_meta( 'description' );
				} // Year
				else if ( is_year() ) {
					$title       = sprintf( '%s %s <span>%s</span>', esc_html__( 'Year', 'kalium' ), $separator, get_the_date( 'Y' ) );
					$description = '';
				} // Month
				else if ( is_month() ) {
					$title       = sprintf( '%s %s <span>%s</span>', esc_html__( 'Month', 'kalium' ), $separator, get_the_date( 'F Y' ) );
					$description = '';
				} // Day
				else if ( is_day() ) {
					$title       = sprintf( '%s %s <span>%s</span>', esc_html__( 'Month', 'kalium' ), $separator, get_the_date( 'F j, Y' ) );
					$description = '';
				}
			}

			// Title and description
			$args['title']       = $title;
			$args['description'] = $description;

			// When there is WPBakery content in description show without page-heading template
			if ( false !== strpos( $description, 'wpb_column' ) ) {
				echo $description;
			} else {

				// Page heading template
				kalium_get_template( 'global/page-heading.php', $args );
			}
		}
	}
}

/**
 * No posts to show message.
 */
if ( ! function_exists( 'kalium_blog_no_posts_found_message' ) ) {

	function kalium_blog_no_posts_found_message() {

		?>
        <h3 class="no-posts-found"><?php esc_html_e( 'There are no posts to show', 'kalium' ); ?></h3>
		<?php
	}
}

/**
 * Blog archive, posts column wrapper open.
 */
if ( ! function_exists( 'kalium_blog_archive_posts_column_open' ) ) {

	function kalium_blog_archive_posts_column_open() {
		echo '<div class="column column--posts">';
	}
}

/**
 * Blog archive, posts column wrapper close.
 */
if ( ! function_exists( 'kalium_blog_archive_posts_column_close' ) ) {

	function kalium_blog_archive_posts_column_close() {
		echo '</div>';
	}
}

/**
 * Blog posts loop template.
 */
if ( ! function_exists( 'kalium_blog_loop_post_template' ) ) {

	function kalium_blog_loop_post_template() {

		// Current blog template
		$blog_template = kalium_blog_get_template();

		// Post classes
		$classes = [
			'post-item',
			'template-' . $blog_template,
		];

		if ( in_array( $blog_template, [ 'square', 'rounded' ] ) ) {
			$classes[] = 'columned';
		}

		// Args
		$args = [
			'classes' => $classes,
		];

		// Post item template
		kalium_get_template( 'blog/loop/post-item.php', $args );
	}
}

/**
 * Blog archive sidebar.
 */
if ( ! function_exists( 'kalium_blog_sidebar_loop' ) ) {

	function kalium_blog_sidebar_loop() {

		if ( kalium_blog_get_option( 'loop/sidebar/visible' ) ) :

			?>
            <div class="column column--sidebar">
				<?php
				// Show widgets
				kalium_dynamic_sidebar( 'blog_sidebar', 'blog-archive--widgets' );
				?>
            </div>
		<?php

		endif;
	}
}

/**
 * Blog post excerpt.
 */
if ( ! function_exists( 'kalium_blog_post_excerpt' ) ) {

	function kalium_blog_post_excerpt() {

		if ( kalium_blog_get_option( 'loop/post_excerpt' ) ) :

			?>
            <div class="post-excerpt entry-summary">
				<?php the_excerpt(); ?>
            </div>
		<?php

		endif;
	}
}

/**
 * Blog post content.
 */
if ( ! function_exists( 'kalium_blog_post_content' ) ) {

	function kalium_blog_post_content() {
		$show_post_content = kalium_blog_get_option( 'loop/post_excerpt' );

		if ( is_single() ) {
			$show_post_content = kalium_blog_get_option( 'single/post_content' );
		}

		if ( $show_post_content ) :

			?>
            <section class="post-content post-formatting">
				<?php
				// Post content
				echo apply_filters( 'the_content', apply_filters( 'kalium_blog_post_content', get_the_content() ) );

				// Post content pagination
				if ( is_single() ) {
					wp_link_pages( [
						'before'           => '<div class="pagination pagination--post-pagination">',
						'after'            => '</div>',
						'next_or_number'   => 'next',
						'previouspagelink' => sprintf( '%2$s %1$s', esc_html__( 'Previous page', 'kalium' ), '&laquo;' ),
						'nextpagelink'     => sprintf( '%1$s %2$s', esc_html__( 'Next page', 'kalium' ), '&raquo;' ),
					] );
				}
				?>
            </section>
		<?php

		endif;
	}
}

/**
 * Blog post date.
 */
if ( ! function_exists( 'kalium_blog_post_date' ) ) {

	function kalium_blog_post_date() {
		$show_post_date = kalium_blog_get_option( 'loop/post_date' );

		if ( is_single() ) {
			$show_post_date = kalium_blog_get_option( 'single/post_date' );
		}

		if ( $show_post_date ) :

			?>
            <div class="post-meta date updated published">
                <i class="icon icon-basic-calendar"></i>
				<?php the_time( apply_filters( 'kalium_post_date_format', get_option( 'date_format' ) ) ); ?>
            </div>
		<?php

		endif;
	}
}

/**
 * Blog post category.
 */
if ( ! function_exists( 'kalium_blog_post_category' ) ) {

	function kalium_blog_post_category() {
		$show_post_category = kalium_blog_get_option( 'loop/post_category' );

		if ( is_single() ) {
			$show_post_category = kalium_blog_get_option( 'single/post_category' );
		}

		if ( $show_post_category && has_category() ) :

			?>
            <div class="post-meta category">
                <i class="icon icon-basic-folder-multiple"></i>
				<?php the_category( ', ' ); ?>
            </div>
		<?php

		endif;
	}
}

/**
 * Blog post tags.
 */
if ( ! function_exists( 'kalium_blog_post_tags' ) ) {

	function kalium_blog_post_tags() {
		$show_post_tags = kalium_blog_get_option( 'loop/post_tags' );

		if ( $show_post_tags ) :

			?>
            <div class="post-meta tags">
                <i class="icon icon-basic-folder-multiple"></i>
				<?php the_tags( ', ' ); ?>
            </div>
		<?php

		endif;
	}
}

/**
 * Loading indicator for columned standard.
 */
if ( ! function_exists( 'kalium_blog_loop_loading_posts_indicator' ) ) {

	function kalium_blog_loop_loading_posts_indicator() {
		?>
        <div class="loading-posts">
			<?php esc_html_e( 'Loading posts...', 'kalium' ); ?>
        </div>
		<?php
	}
}

/**
 * Single post tags list.
 */
if ( ! function_exists( 'kalium_blog_single_post_tags_list' ) ) {

	function kalium_blog_single_post_tags_list() {

		if ( kalium_blog_get_option( 'single/post_tags' ) && has_tag() ) :

			?>
            <section class="post-tags">
				<?php the_tags( '', ' ', '' ); ?>
            </section>
		<?php

		endif;
	}
}

/**
 * Single post share networks.
 */
if ( ! function_exists( 'kalium_blog_single_post_share_networks' ) ) {

	function kalium_blog_single_post_share_networks() {

		if ( kalium_blog_get_option( 'single/share/visible' ) ) :
			$share_networks = kalium_get_enabled_options( kalium_get_theme_option( 'blog_share_story_networks' ) );
			$share_style = kalium_blog_get_option( 'single/share/style' );
			?>
            <section class="<?php echo sprintf( 'post-share-networks post-share-networks--style-%s', esc_attr( $share_style ) ); ?>">

                <div class="share-title">
					<?php esc_html_e( 'Share:', 'kalium' ); ?>
                </div>

                <div class="networks-list">
					<?php
					foreach ( $share_networks as $network_id => $network_name ) {
						kalium_social_network_share_post_link( $network_id, null, [
							'icon_only' => 'icons' === $share_style,
						] );
					}
					?>
                </div>

            </section>
		<?php

		endif;
	}
}

/**
 * Post author info.
 */
if ( ! function_exists( 'kalium_blog_single_post_author_info' ) ) {

	function kalium_blog_single_post_author_info() {
		global $wp_roles;

		$author_id = get_the_author_meta( 'ID' );
		$userdata  = get_userdata( $author_id );

		$author_description = get_the_author_meta( 'description' );
		$author_url         = get_author_posts_url( $author_id );

		$role_name = $wp_roles->roles[ current( $userdata->roles ) ]['name'];

		$link_target = '_self';

		if ( $_author_url = get_the_author_meta( 'url' ) ) {
			$author_url  = $_author_url;
			$link_target = '_blank';
		}

		$author_url = apply_filters( 'kalium_blog_single_author_url', $author_url );

		?>
        <div class="author-info">

			<?php if ( apply_filters( 'kalium_blog_single_post_author_info_show_image', true ) ) : ?>
                <div class="author-info--image">
                    <a href="<?php echo esc_url( $author_url ); ?>" target="<?php echo $link_target; ?>"<?php echo '_blank' == $link_target ? ' rel="noopener"' : ''; ?>>
						<?php echo kalium_image_placeholder_wrap_element( get_avatar( $author_id, 192 ) ); ?>
                    </a>
                </div>
			<?php endif; ?>

            <div class="author-info--details">
                <a href="<?php echo esc_url( $author_url ); ?>" class="vcard author author-name" target="<?php echo $link_target; ?>"<?php echo '_blank' == $link_target ? ' rel="noopener"' : ''; ?>>
                    <span class="fn"><?php the_author() ?></span>

					<?php if ( apply_filters( 'kalium_blog_single_post_author_info_show_subtitle', true ) ) : ?>
                        <em><?php echo apply_filters( 'kalium_blog_single_post_author_info_subtitle', $role_name ); ?></em>
					<?php endif; ?>
                </a>

				<?php
				/**
				 * Other author info details
				 *
				 * @hooked none
				 */
				do_action( 'kalium_blog_single_post_author_info_details', $author_id, $userdata );
				?>
            </div>

        </div>
		<?php
	}
}

/**
 * Single post author and meta aside.
 */
if ( ! function_exists( 'kalium_blog_single_post_author_and_meta_aside' ) ) {

	function kalium_blog_single_post_author_and_meta_aside() {

		if ( kalium_blog_get_option( 'single/author/visible' ) && in_array( kalium_blog_get_option( 'single/author/placement' ), [
				'left',
				'right'
			] ) ) :

			?>
            <aside class="post--column post-author-meta">

				<?php
				/**
				 * Author post info
				 */
				kalium_blog_single_post_author_info();
				?>

				<?php
				/**
				 * Post meta (date, category and other stuff)
				 *
				 * @hooked kalium_blog_post_date - 10
				 * @hooked kalium_blog_post_category - 20
				 */
				do_action( 'kalium_blog_single_post_meta' );
				?>

            </aside>
		<?php

		endif;
	}
}

/**
 * Post meta below the title.
 */
if ( ! function_exists( 'kalium_blog_single_post_meta_below_title' ) ) {

	function kalium_blog_single_post_meta_below_title() {

		if ( ! kalium_blog_get_option( 'single/author/visible' ) || 'bottom' == kalium_blog_get_option( 'single/author/placement' ) ) :

			?>
            <section class="post-meta-only">

				<?php
				/**
				 * Post meta (date, category and other stuff)
				 *
				 * @hooked kalium_blog_post_date - 10
				 * @hooked kalium_blog_post_category - 20
				 */
				do_action( 'kalium_blog_single_post_meta' );
				?>

            </section>
		<?php

		endif;
	}
}

/**
 * Single post author below the article.
 */
if ( ! function_exists( 'kalium_blog_single_post_author_info_below' ) ) {

	function kalium_blog_single_post_author_info_below() {

		if ( kalium_blog_get_option( 'single/author/visible' ) && 'bottom' == kalium_blog_get_option( 'single/author/placement' ) ) :

			?>
            <section class="post-author">

				<?php
				/**
				 * Author post info
				 */
				kalium_blog_single_post_author_info();
				?>

            </section>
		<?php

		endif;
	}
}

/**
 * Single post sidebar.
 */
if ( ! function_exists( 'kalium_blog_single_post_sidebar' ) ) {

	function kalium_blog_single_post_sidebar() {

		if ( kalium_blog_get_option( 'single/sidebar/visible' ) ) :

			?>
            <aside class="post-sidebar">

				<?php
				// Post sidebar
				$sidebar_id = 'blog_sidebar_single';

				if ( ! is_active_sidebar( $sidebar_id ) ) {
					$sidebar_id = 'blog_sidebar';
				}

				kalium_dynamic_sidebar( $sidebar_id, 'single-post--widgets' );
				?>

            </aside>
		<?php

		endif;
	}
}

/**
 * Single post author description when its shown below.
 */
if ( ! function_exists( 'kalium_blog_single_post_author_info_description' ) ) {

	function kalium_blog_single_post_author_info_description( $author_id, $userdata ) {

		if ( 'bottom' == kalium_blog_get_option( 'single/author/placement' ) ) :
			$description = get_the_author_meta( 'description', $author_id );

			if ( $description ) :

				?>
                <div class="author-info--description">

					<?php echo wpautop( $description ); ?>

                </div>
			<?php

			endif;

		endif;
	}
}

/**
 * Single post image in full-width format.
 */
if ( ! function_exists( 'kalium_blog_single_post_image_full_width' ) ) {

	function kalium_blog_single_post_image_full_width() {
		$show_post_image = kalium_blog_get_option( 'single/post_image/visible' );

		if ( $show_post_image && 'full-width' == kalium_blog_get_option( 'single/post_image/placement' ) ) :

			?>
            <section <?php post_class( [ 'post--full-width-image' ] ); ?>>
				<?php
				_kalium_blog_single_post_image();
				?>
            </section>
		<?php

		endif;
	}
}

/**
 * Comment entry callback (open).
 */
if ( ! function_exists( 'kalium_blog_post_comment_open' ) ) {

	function kalium_blog_post_comment_open( $comment, $args, $depth ) {

		// User avatar
		$comment_avatar = get_avatar( $comment );

		// Time and Date format
		$date_format = get_option( 'date_format', 'F d, Y' );
		$time_format = get_option( 'time_format', 'h:m A' );

		$comment_date = apply_filters( 'kalium_blog_post_comment_date', sprintf( _x( '%s at %s', 'comment submit date', 'kalium' ), get_comment_date( $date_format ), get_comment_date( $time_format ) ), $comment );

		// Parent comment
		$parent_comment_id = $comment->comment_parent;

		// In reply to
		$parent_comment = $parent_comment_id ? get_comment( $parent_comment_id ) : null;

		// Commenter image
		$commenter_image = get_comment_author_url() ? sprintf( '<a href="%s">%s</a>', get_comment_author_url(), $comment_avatar ) : $comment_avatar;

		if ( $parent_comment ) {
			$commenter_image .= '<div class="comment-connector"></div>';
		}

		?>
        <div <?php comment_class(); ?> id="comment-<?php comment_id(); ?>"<?php when_match( null !== $parent_comment, sprintf( 'data-replied-to="comment-%d"', $parent_comment_id ) ); ?>>

            <div class="commenter-image">

				<?php
				// Comment avatar
				echo $commenter_image;
				?>

            </div>

            <div class="commenter-details">

                <div class="name">

					<?php
					// Comment Author
					comment_author();

					// Reply Link
					comment_reply_link( array_merge( $args, [
						'reply_text' => esc_html__( 'reply', 'kalium' ),
						'depth'      => $depth,
						'max_depth'  => $args['max_depth'],
						'before'     => ''
					] ) );
					?>

                </div>

                <div class="date">
					<?php
					// Comment date
					echo $comment_date;
					?>

					<?php
					// Parent comment (in reply to)
					if ( $parent_comment ) :

						?>
                        <div class="in-reply-to">
                            &ndash; <?php echo sprintf( esc_html__( 'In reply to: %s', 'kalium' ), '<span class="replied-to">' . get_comment_author( $parent_comment_id ) . '</span>' ); ?>
                        </div>
					<?php
					endif;
					?>
                </div>

                <div class="comment-text post-formatting">

					<?php
					// Comment text
					comment_text();
					?>

                </div>

            </div>

        </div>
		<?php
	}
}

/**
 * Comment entry callback (close).
 */
if ( ! function_exists( 'kalium_blog_post_comment_close' ) ) {

	function kalium_blog_post_comment_close() {
		// Nothing to do
	}
}
