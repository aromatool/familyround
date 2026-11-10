<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Captures "anunță-mă când pot comanda" emails while the site is in prelaunch
 * mode (see FR_PRELAUNCH in the theme) — no order, no payment, just a lead.
 */
class FR_Prelaunch {

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route(
			'familyround/v1',
			'/prelaunch-lead',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_lead' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function handle_lead( WP_REST_Request $request ) {
		$email = sanitize_email( $request->get_param( 'email' ) );
		if ( ! is_email( $email ) ) {
			return new WP_Error( 'fr_invalid_email', 'Adresă de email invalidă.', array( 'status' => 400 ) );
		}
		$email = strtolower( $email );

		$rate_key = 'fr_prelaunch_' . md5( $_SERVER['REMOTE_ADDR'] ?? 'unknown' );
		if ( (int) get_transient( $rate_key ) >= 10 ) {
			return new WP_Error( 'fr_rate_limited', 'Prea multe încercări. Mai încearcă puțin mai târziu.', array( 'status' => 429 ) );
		}
		set_transient( $rate_key, (int) get_transient( $rate_key ) + 1, HOUR_IN_SECONDS );

		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			"INSERT IGNORE INTO {$wpdb->prefix}fr_prelaunch_leads (email, product_id, created_at) VALUES (%s, %d, %s)",
			$email,
			(int) $request->get_param( 'product_id' ),
			current_time( 'mysql' )
		) );

		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}
}
