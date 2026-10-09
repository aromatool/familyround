<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FR_THEME_VERSION', '0.1.0' );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'woocommerce' );

	register_nav_menus( array(
		'primary' => 'Meniu principal',
	) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'familyround-style', get_stylesheet_uri(), array(), FR_THEME_VERSION );

	$asset_path = get_theme_file_path( 'assets/dist/main.js' );
	if ( file_exists( $asset_path ) ) {
		wp_enqueue_script( 'familyround-islands', get_theme_file_uri( 'assets/dist/main.js' ), array(), FR_THEME_VERSION, true );
		wp_localize_script( 'familyround-islands', 'frData', array(
			'restUrl' => esc_url_raw( rest_url( 'familyround/v1' ) ),
		) );
	}

	$css_path = get_theme_file_path( 'assets/dist/main.css' );
	if ( file_exists( $css_path ) ) {
		wp_enqueue_style( 'familyround-islands', get_theme_file_uri( 'assets/dist/main.css' ), array(), FR_THEME_VERSION );
	}
} );
