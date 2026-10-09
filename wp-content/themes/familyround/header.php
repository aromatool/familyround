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
		<a class="fr-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">FamilyRound</a>
		<nav class="fr-nav">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'fallback_cb'    => false,
			) );
			?>
		</nav>
		<a class="fr-header-cta" href="<?php echo esc_url( $fr_shop_url ); ?>">Comandă</a>
	</div>
</header>
