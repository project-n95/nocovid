<?php
/**
 * Kalium WordPress Theme
 *
 * Page heading title and description
 *
 * @var string $heading_tag
 * @var string $title
 * @var string $description
 *
 * @author  Laborator
 * @version 3.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
?>
<section class="page-heading" role="heading">

    <div class="container">

        <div class="row">

			<?php
			/**
			 * Before page heading title hooks
			 */
			do_action( 'page_heading_title_before' );
			?>

            <div class="page-heading__title-section page-heading--title-section section-title">

				<?php if ( $title ) : ?>

					<?php echo sprintf( '<%1$s class="page-heading__title page-heading--title">%2$s</%1$s>', $heading_tag, $title ); ?>

				<?php endif; ?>

				<?php if ( $description ) : ?>

                    <div class="page-heading__description page-heading--description">

						<?php echo $description; ?>

                    </div>

				<?php endif; ?>

            </div>

			<?php
			/**
			 * After page heading title hooks
			 */
			do_action( 'page_heading_title_after' );
			?>

        </div>

    </div>

</section>