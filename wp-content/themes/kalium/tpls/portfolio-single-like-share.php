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

if ( ! ( isset( $portfolio_args['share'] ) && $portfolio_args['share'] || isset( $portfolio_args['likes'] ) && $portfolio_args['likes'] ) ) {
	return;
}

$portfolio_like_share_layout = $portfolio_args['share_layout'];

// Like Icon Class
$like_icon_default = 'far fa-heart';
$like_icon_liked   = 'fas fa-heart';

switch ( $portfolio_args['likes_icon'] ) {
	// Star Icon
	case 'star':
		$like_icon_default = 'far fa-star';
		$like_icon_liked   = 'fas fa-star';
		break;

	// Thumb Up Icon
	case 'thumb':
		$like_icon_default = 'far fa-thumbs-up';
		$like_icon_liked   = 'fas fa-thumbs-up';
		break;
}

// Default Layout
if ( $portfolio_like_share_layout == 'default' ) :

	?>
    <div class="social-links-plain">

		<?php if ( $portfolio_args['likes'] ) : $likes = get_post_likes(); ?>
            <div class="likes">
                <a href="#" class="like-btn like-icon-<?php echo esc_attr( $portfolio_args['likes_icon'] ); ?>" data-id="<?php echo get_the_id(); ?>">
                    <i class="icon <?php echo $likes['liked'] ? $like_icon_liked : $like_icon_default; ?>"></i>
                    <span class="counter like-count"><?php echo esc_html( $likes['count'] ); ?></span>
                </a>
            </div>
		<?php endif; ?>

		<?php if ( $portfolio_args['share'] ) : ?>
            <div class="share-social">
                <h4><?php _e( 'Share', 'kalium' ); ?></h4>
                <div class="social-links">
					<?php
					foreach ( $portfolio_args['share_networks']['visible'] as $network_id => $network ) :

						if ( $network_id == 'placebo' ) {
							continue;
						}

						kalium_social_network_share_post_link( $network_id, $post_id );

					endforeach;
					?>
                </div>
            </div>
		<?php endif; ?>

    </div>
<?php

endif;

// Rounded Buttons
if ( $portfolio_like_share_layout == 'rounded' ) :

	?>
    <div class="social-links-rounded">

        <div class="social-links">
			<?php if ( $portfolio_args['likes'] ) : $likes = get_post_likes(); ?>
                <a href="#" class="social-share-icon like-btn like-icon-<?php echo esc_attr( $portfolio_args['likes_icon'] ); ?><?php echo $likes['liked'] ? ' is-liked' : ''; ?>" data-id="<?php the_ID(); ?>">
                    <i class="icon fa <?php echo $likes['liked'] ? $like_icon_liked : $like_icon_default; ?>"></i>
                    <span class="like-count"><?php echo esc_html( $likes['count'] ); ?></span>
                </a>
			<?php endif; ?>

			<?php
			if ( $portfolio_args['share'] ) :

				foreach ( $portfolio_args['share_networks']['visible'] as $network_id => $network ) :

					if ( 'placebo' == $network_id ) {
						continue;
					}

					kalium_social_network_share_post_link( $network_id, $post_id, [
						'icon_only' => true,
						'class'     => 'social-share-icon',
					] );

				endforeach;

			endif;
			?>
        </div>

    </div>
<?php

endif;
