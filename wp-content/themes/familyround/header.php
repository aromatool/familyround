<?php if ( ! defined( 'ABSPATH' ) ) exit;

$fr_experiences_url = home_url( '/#experiente' );
$fr_cta_url         = $fr_experiences_url;
$fr_cta_label       = 'Vezi experiențele';
$fr_cta_order_submit = false;
$fr_show_cta = true;

if ( function_exists( 'wc_get_products' ) ) {
	$fr_published_games = wc_get_products( array(
		'limit'  => 2,
		'status' => 'publish',
		'return' => 'ids',
	) );
	if ( 1 === count( $fr_published_games ) ) {
		$fr_cta_url         = get_permalink( $fr_published_games[0] );
		$fr_cta_label       = 'Descoperă experiența';
		// With a single game published, "Experiențe" in the nav should go straight to
		// it too — not to the homepage collection section, which today holds only this
		// same game. Once a second game is live, it reverts to the collection anchor.
		$fr_experiences_url = $fr_cta_url;
	}
}

if ( function_exists( 'is_product' ) && is_product() && false !== stripos( get_the_title( get_queried_object_id() ), '30 de provocări' ) ) {
	$fr_current_product = wc_get_product( get_queried_object_id() );
	$fr_cta_url   = ( fr_is_prelaunch() || ! $fr_current_product ) ? '#fr-product-order' : $fr_current_product->add_to_cart_url();
	$fr_cta_label = 'Comandă acum';
	$fr_cta_order_submit = true;
}

if ( function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty() && ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) ) {
	$fr_cta_url   = wc_get_checkout_url();
	$fr_cta_label = 'Finalizează comanda';
	$fr_cta_order_submit = false;
}
if ( function_exists( 'is_checkout' ) && is_checkout() ) {
	$fr_show_cta = false;
}
if ( is_front_page() ) {
	$fr_cta_label = 'Comandă';
}

$fr_primary_links = array(
	array( 'Experiențe', $fr_experiences_url, function_exists( 'is_shop' ) && ( is_shop() || is_product() ) ),
	array( 'Povestea noastră', home_url( '/povestea-noastra/' ), is_page( 'povestea-noastra' ) ),
	array( 'Pușculița', home_url( '/pusculita-familyround/' ), is_page( 'pusculita-familyround' ) ),
	array( 'Harta', home_url( '/harta-familyround/' ), is_page( 'harta-familyround' ) ),
	array( 'Invită o familie', home_url( '/da-timpul-mai-departe/' ), is_page( 'da-timpul-mai-departe' ) ),
);
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="fr-site-header">
	<div class="fr-container fr-header-inner">
		<a class="fr-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/familyround-symbol-bold-web.svg' ) ); ?>" alt="" class="fr-logo-mark" width="42" height="40">
			FamilyRound
		</a>
		<button class="fr-nav-toggle" type="button" aria-label="Deschide meniul" aria-expanded="false" aria-controls="fr-menu">
			<span class="fr-nav-toggle-bars" aria-hidden="true"></span>
		</button>

		<div class="fr-menu" id="fr-menu">
			<div class="fr-menu-inner">
				<p class="fr-menu-intro">Descoperă FamilyRound</p>
				<nav class="fr-nav" aria-label="Meniu principal">
					<ul>
						<?php foreach ( $fr_primary_links as $fr_link ) : ?>
							<li><a href="<?php echo esc_url( $fr_link[1] ); ?>"<?php echo $fr_link[2] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $fr_link[0] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
				<nav class="fr-menu-secondary" aria-label="Alte pagini">
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a>
				</nav>
				<div class="fr-header-actions">
					<?php if ( function_exists( 'WC' ) && WC()->cart && WC()->cart->get_cart_contents_count() > 0 && ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) && ( ! function_exists( 'is_cart' ) || ! is_cart() ) ) :
						$fr_cart_count = WC()->cart->get_cart_contents_count(); ?>
						<a class="fr-header-cart has-items" href="<?php echo esc_url( wc_get_checkout_url() ); ?>" aria-label="Modifică jocurile din coș: <?php echo esc_attr( $fr_cart_count ); ?> <?php echo 1 === $fr_cart_count ? 'produs' : 'produse'; ?>">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="20" r="1.3"/><circle cx="17" cy="20" r="1.3"/><path d="M3 4h2l2.3 11.4a1 1 0 0 0 1 .8h8.6a1 1 0 0 0 1-.8L20 8H6"/></svg>
							<span class="fr-cart-count"><?php echo esc_html( $fr_cart_count ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( $fr_show_cta ) : ?>
						<a class="fr-header-cta" href="<?php echo esc_url( $fr_cta_url ); ?>"<?php echo $fr_cta_order_submit ? ' data-fr-order-submit' : ''; ?>><?php echo esc_html( $fr_cta_label ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</header>
