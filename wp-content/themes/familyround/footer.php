<?php if ( ! defined( 'ABSPATH' ) ) exit;

// Jocurile → the first real product, else the shop page.
$fr_shop_url   = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/magazin' );
$fr_jocuri_url = $fr_shop_url;
if ( function_exists( 'wc_get_products' ) ) {
	$fr_footer_products = wc_get_products( array( 'limit' => 1, 'orderby' => 'date', 'order' => 'ASC', 'status' => 'publish' ) );
	if ( ! empty( $fr_footer_products ) ) {
		$fr_jocuri_url = get_permalink( $fr_footer_products[0]->get_id() );
	}
}

// Social profiles — rendered only when a real, confirmed URL is set here.
$fr_social = array(
	'Instagram' => '',
	'Facebook'  => '',
);
$fr_social = array_filter( $fr_social );

// "Preferințe cookie" is shown only if a real consent manager is wired to this hook.
$fr_has_cookie_prefs = has_action( 'fr_open_cookie_preferences' ) || function_exists( 'fr_cookie_prefs_url' );
?>
<footer class="fr-site-footer">
	<div class="fr-container fr-footer-cta">
		<h2>Următoarea amintire începe cu voi.</h2>
		<a class="fr-btn-gold" href="<?php echo esc_url( home_url( '/#jocuri' ) ); ?>">Descoperă jocurile →</a>
	</div>

	<div class="fr-container fr-footer-grid">
		<div class="fr-footer-brand">
			<a class="fr-footer-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="FamilyRound — acasă">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/logo-mark.svg' ) ); ?>" alt="" width="36" height="34">
				<span>FamilyRound</span>
			</a>
			<p class="fr-footer-tagline">Jocuri și experiențe pentru timpul împreună.</p>
		</div>

		<nav class="fr-footer-col" aria-label="Descoperă">
			<p class="fr-footer-heading">Descoperă</p>
			<ul>
				<li><a href="<?php echo esc_url( $fr_jocuri_url ); ?>">Jocurile</a></li>
				<li><a href="<?php echo esc_url( home_url( '/povestea-noastra/' ) ); ?>">Povestea noastră</a></li>
				<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
			</ul>
		</nav>

		<nav class="fr-footer-col" aria-label="Împreună">
			<p class="fr-footer-heading">Împreună</p>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/pusculita-familyround/' ) ); ?>">Pușculița</a></li>
				<li><a href="<?php echo esc_url( home_url( '/harta-familyround/' ) ); ?>">Harta FamilyRound</a></li>
				<li><a href="<?php echo esc_url( home_url( '/da-timpul-mai-departe/' ) ); ?>">Recomandă FamilyRound</a></li>
			</ul>
		</nav>

		<nav class="fr-footer-col" aria-label="Informații utile">
			<p class="fr-footer-heading">Informații utile</p>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/politica-de-retur/' ) ); ?>">Livrare și retur</a></li>
				<li><a href="<?php echo esc_url( home_url( '/termeni-si-conditii/' ) ); ?>">Termeni și condiții</a></li>
				<li><a href="<?php echo esc_url( home_url( '/politica-de-confidentialitate/' ) ); ?>">Confidențialitate</a></li>
				<?php if ( $fr_has_cookie_prefs ) : ?>
					<li><a href="<?php echo esc_url( function_exists( 'fr_cookie_prefs_url' ) ? fr_cookie_prefs_url() : '#' ); ?>" data-fr-cookie-prefs>Preferințe cookie</a></li>
				<?php endif; ?>
			</ul>
		</nav>
	</div>

	<div class="fr-footer-bottom">
		<div class="fr-container fr-container--wide fr-footer-bottom-inner">
			<p class="fr-footer-copy">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> FamilyRound</p>
			<?php if ( ! empty( $fr_social ) ) : ?>
				<ul class="fr-footer-social">
					<?php foreach ( $fr_social as $label => $url ) : ?>
						<li><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
