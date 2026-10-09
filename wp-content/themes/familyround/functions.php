<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FR_THEME_VERSION', '0.9.0' );

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
			'restUrl'   => esc_url_raw( rest_url( 'familyround/v1' ) ),
			'assetsUrl' => get_theme_file_uri( 'assets/images/' ),
		) );
	}

	$css_path = get_theme_file_path( 'assets/dist/main.css' );
	if ( file_exists( $css_path ) ) {
		wp_enqueue_style( 'familyround-islands', get_theme_file_uri( 'assets/dist/main.css' ), array(), FR_THEME_VERSION );
	}
} );
