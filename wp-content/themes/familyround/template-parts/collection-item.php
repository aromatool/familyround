<?php
/**
 * Reusable collection item — one game presented from product data.
 *
 * Expected $args (passed via get_template_part):
 *   label      string  small uppercase label
 *   title      string  H3 display title
 *   desc       string  short description
 *   highlights array   "what's inside" lines (e.g. ["30 de cartonașe", "Un poster"])
 *   price_html string  WooCommerce price HTML ('' to hide)
 *   url        string  product permalink
 *   set_img    string  default photo URL (box + cards + poster)
 *   set_alt    string
 *   peek_img   string  second photo URL ('' disables the peek action)
 *   peek_alt   string
 *   peek_label string  secondary action label
 *   back_label string  label once peek is open
 *   steps      array   how-to-play steps: [ ['title'=>, 'text'=>], ... ] ('' to hide)
 *   chips      array   sample challenge names ('' to hide the taste strip)
 *   chips_more int     how many more beyond the shown chips
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fr = wp_parse_args(
	$args,
	array(
		'label'      => 'De făcut împreună',
		'title'      => '',
		'desc'       => '',
		'specs'      => array(),
		'price_html' => '',
		'url'        => '#',
		'set_img'    => '',
		'set_alt'    => '',
		'peek_img'   => '',
		'peek_alt'   => '',
		'peek_label' => 'Aruncă o privire în cutie',
		'back_label' => 'Înapoi la set',
		'steps'      => array(),
		'chips'      => array(),
		'chips_more' => 0,
	)
);

$has_peek = ! empty( $fr['peek_img'] );
?>
<article class="fr-product fr-fade-up">
	<div class="fr-product-media">
		<figure class="fr-product-figure"<?php echo $has_peek ? ' data-fr-peek data-back-label="' . esc_attr( $fr['back_label'] ) . '" data-set-label="' . esc_attr( $fr['peek_label'] ) . '"' : ''; ?>>
			<img class="fr-product-img fr-product-img--set is-active" src="<?php echo esc_url( $fr['set_img'] ); ?>" width="1200" height="800" alt="<?php echo esc_attr( $fr['set_alt'] ); ?>">
			<?php if ( $has_peek ) : ?>
				<img class="fr-product-img fr-product-img--peek" src="<?php echo esc_url( $fr['peek_img'] ); ?>" width="1000" height="666" alt="<?php echo esc_attr( $fr['peek_alt'] ); ?>" loading="lazy">
			<?php endif; ?>
		</figure>
	</div>

	<div class="fr-product-info">
		<?php if ( $fr['label'] ) : ?>
			<p class="fr-product-label"><?php echo esc_html( $fr['label'] ); ?></p>
		<?php endif; ?>

		<h3 class="fr-product-title"><?php echo esc_html( $fr['title'] ); ?></h3>

		<?php if ( $fr['desc'] ) : ?>
			<p class="fr-product-desc"><?php echo esc_html( $fr['desc'] ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $fr['specs'] ) ) : ?>
			<div class="fr-product-specs">
				<?php foreach ( $fr['specs'] as $sp ) : ?>
					<div class="fr-spec">
						<span class="fr-spec-num"><?php echo esc_html( $sp['num'] ); ?></span>
						<span class="fr-spec-label"><?php echo esc_html( $sp['label'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="fr-product-buy">
			<?php if ( $fr['price_html'] ) : ?>
				<span class="fr-product-price"><?php echo wp_kses_post( $fr['price_html'] ); ?></span>
			<?php endif; ?>
			<a class="fr-btn-solid" href="<?php echo esc_url( $fr['url'] ); ?>">Descoperă jocul →</a>
		</div>

		<?php if ( $has_peek ) : ?>
			<button type="button" class="fr-product-peek" data-fr-peek-toggle aria-pressed="false"><?php echo esc_html( $fr['peek_label'] ); ?></button>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $fr['steps'] ) ) : ?>
		<div class="fr-product-steps">
			<p class="fr-product-steps-cap">Cum se joacă</p>
			<ol>
				<?php foreach ( $fr['steps'] as $i => $step ) : ?>
					<li>
						<span class="fr-step-num"><?php echo esc_html( $i + 1 ); ?></span>
						<span class="fr-step-body">
							<strong><?php echo esc_html( $step['title'] ); ?></strong>
							<?php if ( ! empty( $step['text'] ) ) : ?>
								<span><?php echo esc_html( $step['text'] ); ?></span>
							<?php endif; ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $fr['chips'] ) ) : ?>
		<div class="fr-product-taste">
			<p class="fr-product-taste-cap">Câteva dintre provocări</p>
			<ul class="fr-product-chips">
				<?php foreach ( $fr['chips'] as $chip ) : ?>
					<li><?php echo esc_html( $chip ); ?></li>
				<?php endforeach; ?>
				<?php if ( $fr['chips_more'] > 0 ) : ?>
					<li class="fr-chip-more">+<?php echo esc_html( $fr['chips_more'] ); ?> altele</li>
				<?php endif; ?>
			</ul>
		</div>
	<?php endif; ?>
</article>
