<?php
/**
 *	Dribbble Gallery
 *	
 *	Laborator.co
 *	www.laborator.co 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Atts
if( function_exists( 'vc_map_get_attributes' ) ) {
	$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
}

extract( $atts );

$dribbble_gallery_id = uniqid("el_");

$more_link = vc_build_link($more_link);

$shots = WPBakeryShortCode_Lab_Dribbble_Gallery::get_user_shots( $atts );

if ( is_array( $shots ) && $count > 0 && count( $shots ) > $count ) {
    $shots = array_slice( $shots, 0, $count );
}

// Element Class
$class = $this->getExtraClass( $el_class );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class, $this->settings['base'], $atts );

$css_class = "lab-dribbble-gallery portfolio-holder {$css_class}";

?>
<div class="<?php echo esc_attr( $css_class ) . vc_shortcode_custom_css_class( $css, ' ' ); ?>">
	<div id="<?php echo esc_attr( $dribbble_gallery_id ); ?>" class="dribbble-container dribbble-<?php echo esc_attr( $columns ); ?>-columns">

        <?php
        if ( is_array( $shots ) ) :

            if ( ! empty( $shots ) ) :

                echo '<ul>';

                foreach ( $shots as $shot ) :
                    $html_url = $shot->html_url;
                    $title = $shot->title;
                    $image = $shot->images->normal;

	                if ( ! empty( $shot->images->hidpi ) ) {
		                $image = $shot->images->hidpi;
	                }

                    if ( ! empty( $shot->images->two_x ) ) {
                        $image = $shot->images->two_x;
                    }

                    ?>
                    <li class="dribbble_shot">
                        <a href="<?php echo esc_html( $html_url ); ?>">
                            <?php
                                echo kalium()->images->get_image( sprintf(
                                    '<img src="%s" alt="%s" width="400" height="300">',
                                    esc_url( $image ),
                                    esc_html( $title )
                                ) );
                            ?>
                            <h3 class="dribbble-title">
                                <span><?php echo esc_html( $title ); ?></span>
                            </h3>
                        </a>
                    </li>
                    <?php

                endforeach;

                echo '</ul>';

            endif;
        elseif ( is_wp_error( $shots ) ) :

            ?>
            <div class="dribbble-errors">
                <?php
                echo $shots->get_error_message();
                ?>
            </div>
            <?php

        endif;
        ?>

	</div>
	
	<?php if ( $more_link['url'] && $more_link['title'] ) : ?>
	<div class="endless-pagination portfolio-endless-pagination endless-pagination-alignment-center <?php echo isset( $show_effect ) && $show_effect ? $show_effect : ''; ?>">
		<div class="show-more">
			<div class="reveal-button">
				<a href="<?php echo esc_url( $more_link['url'] ); ?>" target="<?php echo esc_attr( $more_link['target'] ); ?>" class="btn btn-white">
					<?php echo esc_html( $more_link['title'] ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>