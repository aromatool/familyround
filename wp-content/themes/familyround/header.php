<?php if ( ! defined( 'ABSPATH' ) ) exit;
$fr_shop_url = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/magazin' );
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
			<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/logo-mark.svg' ) ); ?>" alt="" class="fr-logo-mark" width="42" height="40">
			FamilyRound
		</a>
		<nav class="fr-nav">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'fallback_cb'    => false,
			) );
			?>
		</nav>
		<div class="fr-header-actions">
			<?php if ( function_exists( 'WC' ) && WC()->cart ) :
				$fr_cart_count = WC()->cart->get_cart_contents_count(); ?>
				<a class="fr-header-cart<?php echo $fr_cart_count > 0 ? ' has-items' : ''; ?>" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="Coș de cumpărături: <?php echo esc_attr( $fr_cart_count ); ?> produse">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="20" r="1.3"/><circle cx="17" cy="20" r="1.3"/><path d="M3 4h2l2.3 11.4a1 1 0 0 0 1 .8h8.6a1 1 0 0 0 1-.8L20 8H6"/></svg>
					<span class="fr-cart-count"<?php echo $fr_cart_count > 0 ? '' : ' hidden'; ?>><?php echo esc_html( $fr_cart_count ); ?></span>
				</a>
			<?php endif; ?>
			<a class="fr-header-cta" href="<?php echo esc_url( $fr_shop_url ); ?>">Comandă</a>
		</div>
	</div>
</header>
