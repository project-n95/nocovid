<?php
/**
 * Kalium WordPress Theme
 *
 * F.A.Q page.
 *
 * @var array $faq_articles
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

?>
<div class="about-kalium__heading">
    <h2>Frequently Asked Questions</h2>
    <p>These are frequently asked questions for most common issues our users encounter in theme during their initial use.</p>
</div>


<ul class="about-kalium__faq-articles">

	<?php foreach ( $faq_articles as $i => $faq ) : ?>
        <li class="about-kalium__faq-article-entry" id="faq-<?php echo $faq['id']; ?>">
            <h3 class="about-kalium__faq-article-entry-title">
                <a href="#faq-<?php echo $faq['id']; ?>">
                    <span class="caret"></span>
					<?php echo $faq['title']; ?>
                </a>
            </h3>
            <div class="about-kalium__faq-article-entry-content">
				<?php echo wpautop( $faq['content'] ) ?>

				<?php if ( ! empty( $faq['link'] ) ) : ?>
                    <a href="<?php echo $faq['link']; ?>" target="_blank" class="about-kalium__faq-article-entry-content-link">Read full article &raquo;</a>
				<?php endif; ?>
            </div>
        </li>
	<?php endforeach; ?>

</ul>