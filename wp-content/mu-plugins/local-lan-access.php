<?php
/**
 * Local-only: lets the site load correctly from a phone on the same Wi-Fi
 * (http://<mac-lan-ip>:8081) without touching the real siteurl/home options,
 * which stay pointed at localhost:8081 for normal desktop use. Only kicks in
 * when the request's own Host header is a private LAN address — never a real
 * public domain, so this is safe to leave in place.
 *
 * siteurl/home alone aren't enough: WP_CONTENT_URL (theme/plugin asset URLs)
 * is frozen into a constant at bootstrap, before mu-plugins even load, so it
 * has to be rewritten via its own filters too.
 */

function fr_local_rewrite_url( $url ) {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '';
	if ( ! $host || 0 === strpos( $host, 'localhost' ) ) {
		return $url;
	}
	$ip = explode( ':', $host )[0];
	if ( ! preg_match( '/^(192\.168\.|10\.|172\.(1[6-9]|2\d|3[01])\.)/', $ip ) ) {
		return $url;
	}
	return preg_replace( '#^https?://[^/]+#', 'http://' . $host, $url );
}

foreach ( array( 'option_siteurl', 'option_home', 'content_url', 'plugins_url', 'theme_file_uri', 'stylesheet_directory_uri', 'template_directory_uri', 'stylesheet_uri', 'template_uri' ) as $fr_local_hook ) {
	add_filter( $fr_local_hook, 'fr_local_rewrite_url' );
}
