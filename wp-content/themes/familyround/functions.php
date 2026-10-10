<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FR_THEME_VERSION', '1.19.0' );

function fr_challenge_cards() {
	return array(
		1  => 'Drumeție',
		2  => 'Picnic',
		3  => 'Comoara din Natură',
		4  => 'Planta care Crește cu Voi',
		5  => 'Cu Cortul în Sufragerie',
		6  => 'Povești la Lumina Lanternei',
		7  => 'Inversați Rolul pentru o Zi',
		8  => 'Coșul cu Surprize',
		9  => 'Expediția cu Monedă',
		10 => 'Refaceți o Fotografie Veche',
		11 => 'Scrisoarea de Peste un An',
		12 => 'Amprenta Familiei',
		13 => 'Călătorie în Copilăria Părinților',
		14 => 'Misiunea Face Lumea mai Frumoasă',
		15 => 'O Excursie cu Trenul',
		16 => 'Surprindeți Răsăritul Împreună',
		17 => 'Construim un Vehicul',
		18 => 'Zbor cu Zmeie',
		19 => 'Arborele Genealogic',
		20 => 'O Zi la Planetariu sau Observator',
		21 => 'Ziua în Care Construiți Jucării',
		22 => 'Din Natură în Borcan',
		23 => 'Zi de Magie',
		24 => 'Construiți o Plută',
		25 => 'Restaurant Familie',
		26 => 'Lăsați Ceva în Urmă',
		27 => 'Vânătoare de Comori',
		28 => 'Faceți o Surpriză Bunicilor',
		29 => 'Karaoke',
		30 => 'Blazonul Familiei',
	);
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'woocommerce' );

	register_nav_menus( array(
		'primary' => 'Meniu principal',
		'footer'  => 'Meniu footer',
	) );
} );

/**
 * Discreet line icons for the primary menu.
 * Matched by the item's label/URL (diacritic-insensitive), so renaming is tolerated.
 */
function fr_nav_icon_svg( $key ) {
	$icons = array(
		'map'   => '<path d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11Z"/><circle cx="12" cy="10" r="2.4"/>',
		'cards' => '<rect x="8" y="4" width="11" height="15" rx="2"/><path d="M5 7v10a3 3 0 0 0 3 3h7"/>',
		'heart' => '<path d="M12 20s-6.6-4.2-9-8.4C1.4 8.3 3.1 5 6.3 5 8.2 5 9.6 6.2 12 8.6 14.4 6.2 15.8 5 17.7 5c3.2 0 4.9 3.3 3.3 6.6C18.6 15.8 12 20 12 20Z"/>',
		'book'  => '<path d="M4 5a2 2 0 0 1 2-2h6v16H6a2 2 0 0 0-2 2V5Z"/><path d="M20 5a2 2 0 0 0-2-2h-6v16h6a2 2 0 0 1 2 2V5Z"/>',
		'mail'  => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
	);
	if ( empty( $icons[ $key ] ) ) {
		return '';
	}
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' . $icons[ $key ] . '</svg>';
}

function fr_nav_icon_key( $item ) {
	$t = remove_accents( strtolower( (string) $item->title ) );
	$u = strtolower( (string) $item->url );
	if ( false !== strpos( $t, 'hart' ) || false !== strpos( $u, 'harta' ) ) return 'map';
	if ( false !== strpos( $t, 'pusculi' ) || false !== strpos( $u, 'pusculita' ) ) return 'heart';
	if ( false !== strpos( $t, 'poveste' ) || false !== strpos( $u, 'povestea' ) ) return 'book';
	if ( false !== strpos( $t, 'contact' ) || false !== strpos( $u, 'contact' ) ) return 'mail';
	if ( false !== strpos( $t, 'joc' ) || false !== strpos( $u, 'shop' ) || false !== strpos( $u, 'product' ) || false !== strpos( $u, 'magazin' ) ) return 'cards';
	return '';
}

add_filter( 'nav_menu_item_title', function ( $title, $item, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $title;
	}
	$svg = fr_nav_icon_svg( fr_nav_icon_key( $item ) );
	return $svg ? '<span class="fr-nav-ico" aria-hidden="true">' . $svg . '</span>' . $title : $title;
}, 10, 3 );

add_filter( 'wp_resource_hints', function ( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}, 10, 2 );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'familyround-fonts',
		'https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;0,900;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'familyround-style', get_stylesheet_uri(), array( 'familyround-fonts' ), FR_THEME_VERSION );

	wp_enqueue_script( 'familyround-interactions', get_theme_file_uri( 'assets/interactions.js' ), array(), FR_THEME_VERSION, true );

	$asset_path = get_theme_file_path( 'assets/dist/main.js' );
	if ( file_exists( $asset_path ) ) {
		wp_enqueue_script( 'familyround-islands', get_theme_file_uri( 'assets/dist/main.js' ), array(), FR_THEME_VERSION, true );
		wp_localize_script( 'familyround-islands', 'frData', array(
			'restUrl'      => esc_url_raw( rest_url( 'familyround/v1' ) ),
			'assetsUrl'    => get_theme_file_uri( 'assets/images/' ),
			'pusculitaUrl' => esc_url_raw( home_url( '/pusculita-familyround/' ) ),
		) );
	}

	$css_path = get_theme_file_path( 'assets/dist/main.css' );
	if ( file_exists( $css_path ) ) {
		wp_enqueue_style( 'familyround-islands', get_theme_file_uri( 'assets/dist/main.css' ), array(), FR_THEME_VERSION );
	}
} );
