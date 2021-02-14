<?php
/**
 * Kalium WordPress Theme
 *
 * Laborator.co
 * www.laborator.co
 *
 * @deprecated 3.0 This template file will be removed or replaced with new one in templates/ folder.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

include locate_template( 'tpls/portfolio-single-item-details.php' );

// Enqueue nivo
kalium_enqueue( 'nivo' );

// 1/3 column width
$column_widths = [
	'description-width'    => 'col-md-4',
	'gallery-width'        => 'col-md-7'
];

// 1/2 column width
if ( $description_width == '1-2' ) {
	$column_widths['description-width']    = 'col-md-5';
	$column_widths['gallery-width']        = 'col-md-6';
}

// 1/4 column width
if ( $description_width == '1-4' ) {
	$column_widths['description-width']    = 'col-md-3';
	$column_widths['gallery-width']        = 'col-md-8';
}

// Columns Gap
if ( $gallery_columns_gap ) {
	kalium_portfolio_generate_gallery_gap( $gallery_columns_gap );
}

do_action( 'kalium_portfolio_item_before', 'type-1' ); 
?>
<div class="container">

	<div class="page-container<?php 
		echo $gallery_type == 'fullbg' ? ' no-bottom-margin' : '';
	?>">

		<div class="single-portfolio-holder portfolio-type-1 alt-one clearfix<?php
			echo $gallery_type == 'fullbg' ? ' gallery-type-fullbg' : '';
			echo $gallery_type == 'fullbg' && ! $gallery_stick_to_top ? ' gallery-no-top-stick' : '';
			echo $sticky_description ? ' is-sticky' : '';
			echo $description_alignment == 'left' ? ' description-set-left' : '';
		?>">

			<div class="details <?php
				echo esc_attr( $column_widths['description-width'] );
				echo $description_alignment == 'right' ? ' col-md-offset-1 pull-right-md' : '';
			?>">
				<?php
				do_action( 'kalium_portfolio_type_side_portfolio_before_title' );
				?>

				<div class="title section-title">
					<h1><?php the_title(); ?></h1>

					<?php if ( $sub_title ) : ?>
					<p><?php echo wp_kses_post( $sub_title ); ?></p>
					<?php endif; ?>
				</div>

				<div class="project-description">
					<div class="post-formatting">
						<?php the_content(); ?>
					</div>
				</div>

				<?php include locate_template( 'tpls/portfolio-checklists.php' ); ?>

				<?php include locate_template( 'tpls/portfolio-launch-project.php' ); ?>

				<?php include locate_template( 'tpls/portfolio-single-like-share.php' ); ?>

			</div>

			<div class="<?php echo esc_attr( $column_widths['gallery-width'] ); echo $description_alignment == 'left' ? ' col-md-offset-1' : ''; ?> gallery-column-env">

				<?php include locate_template( 'tpls/portfolio-gallery.php' ); ?>

			</div>

			<?php include locate_template( 'tpls/portfolio-single-prevnext.php' ); ?>
		</div>
	</div>

</div>