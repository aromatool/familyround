<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Programul de recomandări — "Dă timpul mai departe".
 * Cookie-ul de atribuire + înscrierea + dashboard-ul privat prin token.
 */
class FR_Referrals {

	const COOKIE_NAME = 'fr_ref';
	const COOKIE_DAYS = 30;

	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_capture_referral_click' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Un vizitator care vine cu ?ref=COD primește un cookie de atribuire (30 de zile).
	 * Codul introdus manual la checkout are mereu prioritate față de acest cookie.
	 */
	public static function maybe_capture_referral_click() {
		if ( empty( $_GET['ref'] ) || headers_sent() ) {
			return;
		}

		$code = self::normalize_code( wp_unslash( $_GET['ref'] ) );

		global $wpdb;
		$referrer = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}fr_referrers WHERE referral_code = %s",
				$code
			)
		);

		if ( ! $referrer ) {
			return;
		}

		setcookie( self::COOKIE_NAME, $code, time() + self::COOKIE_DAYS * DAY_IN_SECONDS, '/' );

		$cookie_hash = hash( 'sha256', $code . '|' . ( $_SERVER['REMOTE_ADDR'] ?? '' ) . '|' . gmdate( 'Y-m-d' ) );
		$wpdb->query(
			$wpdb->prepare(
				"INSERT IGNORE INTO {$wpdb->prefix}fr_referral_clicks (referrer_id, cookie_hash, created_at) VALUES (%d, %s, %s)",
				$referrer->id,
				$cookie_hash,
				current_time( 'mysql' )
			)
		);
	}

	public static function register_routes() {
		register_rest_route(
			'familyround/v1',
			'/referrals',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_signup' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'familyround/v1',
			'/referrals/me',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'handle_dashboard' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function handle_signup( WP_REST_Request $request ) {
		$first_name = sanitize_text_field( $request->get_param( 'first_name' ) );
		$email      = sanitize_email( $request->get_param( 'email' ) );

		if ( ! $first_name || ! is_email( $email ) ) {
			return new WP_Error( 'fr_invalid_signup', 'Prenume și email valide sunt obligatorii.', array( 'status' => 400 ) );
		}

		global $wpdb;
		$table    = $wpdb->prefix . 'fr_referrers';
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE email = %s", $email ) );

		$token      = bin2hex( random_bytes( 32 ) );
		$token_hash = hash( 'sha256', $token );

		if ( $existing ) {
			$wpdb->update( $table, array( 'token_hash' => $token_hash ), array( 'id' => $existing->id ) );
			$code = $existing->referral_code;
		} else {
			$code = self::generate_unique_code();
			$wpdb->insert(
				$table,
				array(
					'first_name'    => $first_name,
					'email'         => $email,
					'referral_code' => $code,
					'token_hash'    => $token_hash,
					'created_at'    => current_time( 'mysql' ),
				)
			);
		}

		$link      = add_query_arg( 'ref', $code, home_url( '/' ) );
		$dashboard = add_query_arg( 'token', $token, home_url( '/da-timpul-mai-departe/' ) );

		$body = "Salut, {$first_name}!\n\nLinkul tău FamilyRound: {$link}\nDashboard-ul tău: {$dashboard}\n\nMulțumim că dai timpul mai departe!";
		wp_mail( $email, 'Linkul tău de recomandare FamilyRound', $body );

		return new WP_REST_Response(
			array(
				'code'      => $code,
				'link'      => $link,
				'dashboard' => $dashboard,
			),
			200
		);
	}

	public static function handle_dashboard( WP_REST_Request $request ) {
		$token = sanitize_text_field( $request->get_param( 'token' ) );

		if ( ! $token ) {
			return new WP_Error( 'fr_missing_token', 'Token lipsă.', array( 'status' => 400 ) );
		}

		global $wpdb;
		$token_hash = hash( 'sha256', $token );
		$referrer   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}fr_referrers WHERE token_hash = %s",
				$token_hash
			)
		);

		if ( ! $referrer ) {
			return new WP_Error( 'fr_invalid_token', 'Link invalid sau expirat.', array( 'status' => 404 ) );
		}

		$validated_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}fr_referral_orders WHERE referrer_id = %d AND status = 'valid'",
				$referrer->id
			)
		);

		$total_bonus = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(bonus_amount), 0) FROM {$wpdb->prefix}fr_referral_orders WHERE referrer_id = %d AND status = 'valid'",
				$referrer->id
			)
		);

		return new WP_REST_Response(
			array(
				'first_name'      => $referrer->first_name,
				'code'            => $referrer->referral_code,
				'link'            => add_query_arg( 'ref', $referrer->referral_code, home_url( '/' ) ),
				'validated_count' => $validated_count,
				'total_bonus'     => $total_bonus,
			),
			200
		);
	}

	private static function normalize_code( $code ) {
		return strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $code ) );
	}

	private static function generate_unique_code() {
		global $wpdb;
		$table = $wpdb->prefix . 'fr_referrers';

		do {
			$code = strtoupper( substr( bin2hex( random_bytes( 4 ) ), 0, 6 ) );
			$found = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE referral_code = %s", $code ) );
		} while ( $found );

		return $code;
	}
}
