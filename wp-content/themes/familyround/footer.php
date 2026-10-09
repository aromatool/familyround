<?php if ( ! defined( 'ABSPATH' ) ) exit;

$fr_shop_url   = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/magazin' );
$fr_jocuri_url = $fr_shop_url;
if ( function_exists( 'wc_get_products' ) ) {
	$fr_footer_products = wc_get_products( array( 'limit' => 1, 'orderby' => 'date', 'order' => 'ASC', 'status' => 'publish' ) );
	if ( ! empty( $fr_footer_products ) ) {
		$fr_jocuri_url = get_permalink( $fr_footer_products[0]->get_id() );
	}
}
?>
<footer class="fr-site-footer">
	<div class="fr-container fr-footer-grid">
		<div class="fr-footer-brand">
			<a class="fr-logo fr-footer-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/logo-icon.png' ) ); ?>" alt="" class="fr-logo-mark" width="28" height="27">
				FamilyRound
			</a>
			<p class="fr-footer-tagline">Mai mult timp, împreună.</p>
		</div>

		<div class="fr-footer-col">
			<p class="fr-footer-heading">Explorează</p>
			<ul>
				<li><a href="<?php echo esc_url( $fr_jocuri_url ); ?>">Jocuri</a></li>
				<li><a href="<?php echo esc_url( home_url( '/pusculita-familyround/' ) ); ?>">Pușculița FamilyRound</a></li>
				<li><a href="<?php echo esc_url( home_url( '/harta-familyround/' ) ); ?>">Harta FamilyRound</a></li>
				<li><a href="<?php echo esc_url( home_url( '/da-timpul-mai-departe/' ) ); ?>">Dă timpul mai departe</a></li>
			</ul>
		</div>

		<div class="fr-footer-col">
			<p class="fr-footer-heading">Companie</p>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/povestea-noastra/' ) ); ?>">Povestea noastră</a></li>
				<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
			</ul>
		</div>

		<div class="fr-footer-col">
			<p class="fr-footer-heading">Legal &amp; suport</p>
			<nav class="fr-footer-links">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'container'      => false,
					'fallback_cb'    => false,
					'items_wrap'     => '<ul>%3$s</ul>',
				) );
				?>
			</nav>
		</div>
	</div>

	<div class="fr-footer-bottom">
		<div class="fr-container">
			<p>&copy; <?php echo esc_html( date( 'Y' ) ); ?> FamilyRound. Toate drepturile rezervate.</p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
