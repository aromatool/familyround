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
		add_action( 'template_redirect', array( __CLASS__, 'protect_private_dashboard' ) );
	}

	public static function protect_private_dashboard() {
		if ( is_page( 'da-timpul-mai-departe' ) && ( isset( $_GET['token'] ) || isset( $_GET['code'] ) ) ) {
			nocache_headers();
			if ( isset( $_GET['token'] ) ) {
				header( 'Referrer-Policy: no-referrer' );
			}
		}
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

		setcookie( self::COOKIE_NAME, $code, array(
			'expires'  => time() + self::COOKIE_DAYS * DAY_IN_SECONDS,
			'path'     => '/',
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		) );

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
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_dashboard' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'familyround/v1',
			'/referrals/public',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_public_stats' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/** A referral code is public; this endpoint returns only aggregate referral impact. */
	public static function handle_public_stats( WP_REST_Request $request ) {
		nocache_headers();
		$raw_code = $request->get_param( 'code' );
		$code = is_string( $raw_code ) ? self::normalize_code( $raw_code ) : '';
		if ( ! $code ) {
			return new WP_Error( 'fr_missing_code', 'Introdu codul de recomandare.', array( 'status' => 400 ) );
		}
		global $wpdb;
		$referrer_id = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$wpdb->prefix}fr_referrers WHERE referral_code = %s",
			$code
		) );
		if ( ! $referrer_id ) {
			return new WP_Error( 'fr_unknown_code', 'Nu am găsit acest cod. Verifică-l și încearcă din nou.', array( 'status' => 404 ) );
		}
		$impact = $wpdb->get_row( $wpdb->prepare(
			"SELECT COUNT(*) AS validated_count, COALESCE(SUM(bonus_amount), 0) AS total_bonus
			 FROM {$wpdb->prefix}fr_referral_orders WHERE referrer_id = %d AND status = 'valid'",
			$referrer_id
		) );
		$total_bonus = (float) $impact->total_bonus;
		return new WP_REST_Response( array(
			'code'            => $code,
			'link'            => self::referral_link( $code ),
			'validated_count' => (int) $impact->validated_count,
			'eligible_games'  => (int) round( $total_bonus / FR_Orders::BONUS_AMOUNT ),
			'total_bonus'     => $total_bonus,
		), 200 );
	}

	public static function handle_signup( WP_REST_Request $request ) {
		$first_name = sanitize_text_field( $request->get_param( 'first_name' ) );
		$email      = sanitize_email( $request->get_param( 'email' ) );

		if ( ! $first_name || ! is_email( $email ) ) {
			return new WP_Error( 'fr_invalid_signup', 'Prenume și email valide sunt obligatorii.', array( 'status' => 400 ) );
		}
		$email = strtolower( $email );
		$rate_key = 'fr_ref_signup_' . md5( $email );
		$source_key = 'fr_ref_source_' . md5( $_SERVER['REMOTE_ADDR'] ?? 'unknown' );
		if ( get_transient( $rate_key ) || (int) get_transient( $source_key ) >= 10 ) {
			return new WP_Error( 'fr_signup_recent', 'Ți-am trimis deja un email. Verifică și folderul Spam sau încearcă din nou peste un minut.', array( 'status' => 429 ) );
		}
		set_transient( $rate_key, 1, MINUTE_IN_SECONDS );
		set_transient( $source_key, (int) get_transient( $source_key ) + 1, HOUR_IN_SECONDS );

		$referrer  = self::upsert_referrer_with_fresh_token( $first_name, $email );
		if ( is_wp_error( $referrer ) ) {
			return $referrer;
		}
		$link      = self::referral_link( $referrer->referral_code );
		$stats = self::public_stats_link( $referrer->referral_code );
		$wants_updates = rest_sanitize_boolean( $request->get_param( 'updates_opt_in' ) );

		$body = "Salut, {$first_name}!\n\nCodul tău FamilyRound este {$referrer->referral_code}. Păstrează acest email ca să îl găsești oricând.\n\nLinkul de trimis mai departe: {$link}\n\nCând vrei să vezi câte recomandări s-au confirmat și câți lei am pus deoparte prin codul tău, intră aici: {$stats}\n\nLa prima comandă a fiecărui cumpărător venit prin linkul tău, FamilyRound adaugă încă 5 lei pentru fiecare joc eligibil în Pușculiță. Codul tău poate fi folosit de oricâte familii noi. Nimeni nu plătește în plus.\n\nMulțumim că dai timpul mai departe!";
		if ( $wants_updates ) {
			$body .= "\n\nAi cerut și actualizări despre recomandări. Vei primi cel mult un email pe săptămână, numai când apar comenzi noi validate. Fiecare actualizare va include un link de dezabonare.";
		}
		if ( ! wp_mail( $email, 'Codul tău FamilyRound, de păstrat și de dat mai departe', $body ) ) {
			return new WP_Error( 'fr_signup_mail_failed', 'Nu am putut trimite emailul acum. Te rugăm să încerci din nou.', array( 'status' => 503 ) );
		}
		if ( $wants_updates ) {
			self::opt_in_email( $first_name, $email );
		}
		return new WP_REST_Response(
			array(
				'code'      => $referrer->referral_code,
				'link'      => $link,
			),
			200
		);
	}

	/**
	 * Creează sau reînnoiește un recomandator — folosit atât de formularul public,
	 * cât și automat, pentru orice cumpărător (fiecare client devine un recomandator eligibil).
	 * Token-ul se reînnoiește de fiecare dată: ultimul email trimis conține mereu linkul valid.
	 */
	public static function upsert_referrer_with_fresh_token( $first_name, $email ) {
		global $wpdb;
		$table    = $wpdb->prefix . 'fr_referrers';
		$email    = strtolower( sanitize_email( $email ) );
		if ( ! is_email( $email ) ) {
			return new WP_Error( 'fr_invalid_email', 'Adresa de email este invalidă.', array( 'status' => 400 ) );
		}
		self::maybe_prune_tokens();
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE email = %s", $email ) );

		$token      = bin2hex( random_bytes( 32 ) );
		$token_hash = hash( 'sha256', $token );

		if ( $existing ) {
			if ( false === $wpdb->update( $table, array( 'token_hash' => $token_hash ), array( 'id' => $existing->id ) ) ) {
				return new WP_Error( 'fr_referrer_save_failed', 'Nu am putut salva linkul. Încearcă din nou.', array( 'status' => 503 ) );
			}
			$code = $existing->referral_code;
			$referrer_id = (int) $existing->id;
		} else {
			$referrer_id = 0;
			for ( $attempt = 0; $attempt < 5; $attempt++ ) {
				$code = self::generate_unique_code();
				$inserted = $wpdb->insert( $table, array(
					'first_name'    => $first_name,
					'email'         => $email,
					'referral_code' => $code,
					'token_hash'    => $token_hash,
					'created_at'    => current_time( 'mysql' ),
				) );
				if ( $inserted ) {
					$referrer_id = (int) $wpdb->insert_id;
					break;
				}
				$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE email = %s", $email ) );
				if ( $existing ) {
					$referrer_id = (int) $existing->id;
					$code = $existing->referral_code;
					if ( false === $wpdb->update( $table, array( 'token_hash' => $token_hash ), array( 'id' => $referrer_id ) ) ) {
						return new WP_Error( 'fr_referrer_save_failed', 'Nu am putut salva linkul. Încearcă din nou.', array( 'status' => 503 ) );
					}
					break;
				}
			}
			if ( ! $referrer_id ) {
				return new WP_Error( 'fr_referrer_save_failed', 'Nu am putut crea linkul. Încearcă din nou.', array( 'status' => 503 ) );
			}
		}
		if ( ! $wpdb->insert( $wpdb->prefix . 'fr_referrer_tokens', array(
				'token_hash'  => $token_hash,
				'referrer_id' => $referrer_id,
				'created_at'  => current_time( 'mysql' ),
			) ) ) {
			return new WP_Error( 'fr_token_save_failed', 'Nu am putut salva accesul privat. Încearcă din nou.', array( 'status' => 503 ) );
		}

		return (object) array(
			'referral_code' => $code,
			'token'         => $token,
			'first_name'    => $first_name,
			'email'         => $email,
		);
	}

	public static function referral_link( $code ) {
		return add_query_arg( 'ref', $code, home_url( '/' ) );
	}

	public static function public_stats_link( $code ) {
		return add_query_arg( 'code', $code, home_url( '/da-timpul-mai-departe/' ) );
	}

	/** Explicit, unchecked-by-default consent. Start the digest from today's totals. */
	public static function opt_in_email( $first_name, $email ) {
		global $wpdb;
		$email = strtolower( sanitize_email( $email ) );
		if ( ! is_email( $email ) ) return false;
		$table = $wpdb->prefix . 'fr_referrers';
		$referrer = $wpdb->get_row( $wpdb->prepare( "SELECT id, updates_opt_in FROM {$table} WHERE email = %s", $email ) );
		if ( ! $referrer ) {
			$created = self::upsert_referrer_with_fresh_token( $first_name, $email );
			if ( is_wp_error( $created ) ) return false;
			$referrer = $wpdb->get_row( $wpdb->prepare( "SELECT id, updates_opt_in FROM {$table} WHERE email = %s", $email ) );
		}
		if ( ! $referrer || (int) $referrer->updates_opt_in ) return (bool) $referrer;
		$impact = $wpdb->get_row( $wpdb->prepare(
			"SELECT COUNT(*) AS orders, COALESCE(SUM(bonus_amount), 0) AS bonus FROM {$wpdb->prefix}fr_referral_orders WHERE referrer_id = %d AND status = 'valid'",
			$referrer->id
		) );
		return false !== $wpdb->update( $table, array(
			'updates_opt_in'    => 1,
			'updates_consent_at' => current_time( 'mysql' ),
			'digest_count'       => (int) $impact->orders,
			'digest_bonus'       => (float) $impact->bonus,
		), array( 'id' => $referrer->id ) );
	}

	/**
	 * Returnează linkul unui cumpărător fără să reînnoiască token-ul privat.
	 * Pagina de mulțumire poate fi reîncărcată după emailul comenzii; token-ul
	 * din acel email trebuie să rămână valid.
	 */
	public static function link_for_customer( $first_name, $email ) {
		if ( ! is_email( $email ) ) {
			return '';
		}
		global $wpdb;
		$code = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT referral_code FROM {$wpdb->prefix}fr_referrers WHERE email = %s",
				$email
			)
		);
		if ( ! $code ) {
			$referrer = self::upsert_referrer_with_fresh_token( $first_name, $email );
			if ( is_wp_error( $referrer ) ) {
				return '';
			}
			$code = $referrer->referral_code;
		}
		return self::referral_link( $code );
	}

	public static function dashboard_link( $token ) {
		return add_query_arg( 'token', $token, home_url( '/da-timpul-mai-departe/' ) );
	}

	public static function handle_dashboard( WP_REST_Request $request ) {
		nocache_headers();
		self::maybe_prune_tokens();
		$token = sanitize_text_field( $request->get_param( 'token' ) );

		if ( ! $token ) {
			return new WP_Error( 'fr_missing_token', 'Token lipsă.', array( 'status' => 400 ) );
		}

		global $wpdb;
		$token_hash = hash( 'sha256', $token );
		$referrer   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT r.* FROM {$wpdb->prefix}fr_referrer_tokens t
				 INNER JOIN {$wpdb->prefix}fr_referrers r ON r.id = t.referrer_id
				 WHERE t.token_hash = %s AND t.created_at >= %s",
				$token_hash,
				wp_date( 'Y-m-d H:i:s', time() - 180 * DAY_IN_SECONDS )
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
		$own_amount = 0.0;
		if ( function_exists( 'wc_get_orders' ) ) {
			$order_ids = wc_get_orders( array(
				'billing_email' => $referrer->email,
				'status'        => array( 'processing', 'completed' ),
				'limit'         => -1,
				'return'        => 'ids',
			) );
			if ( $order_ids ) {
				$placeholders = implode( ',', array_fill( 0, count( $order_ids ), '%d' ) );
				$own_amount = (float) $wpdb->get_var( $wpdb->prepare(
					"SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}fr_piggybank_ledger
					 WHERE order_id IN ({$placeholders}) AND status != 'cancelled'
					 AND (entry_type = 'vanzare' OR entry_type LIKE 'ajustare_v_%')",
					...array_map( 'intval', $order_ids )
				) );
			}
		}

		return new WP_REST_Response(
			array(
				'first_name'      => $referrer->first_name,
				'code'            => $referrer->referral_code,
				'link'            => self::referral_link( $referrer->referral_code ),
				'validated_count' => $validated_count,
				'eligible_games'  => (int) round( $total_bonus / FR_Orders::BONUS_AMOUNT ),
				'total_bonus'     => $total_bonus,
				'own_amount'      => $own_amount,
				'total_impact'    => $own_amount + $total_bonus,
			),
			200
		);
	}

	private static function maybe_prune_tokens() {
		if ( ! get_transient( 'fr_ref_tokens_pruned' ) ) {
			global $wpdb;
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}fr_referrer_tokens WHERE created_at < %s",
				wp_date( 'Y-m-d H:i:s', time() - 180 * DAY_IN_SECONDS )
			) );
			set_transient( 'fr_ref_tokens_pruned', 1, DAY_IN_SECONDS );
		}
	}

	public static function normalize_code( $code ) {
		return substr( strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', (string) $code ) ), 0, 20 );
	}

	private static function generate_unique_code() {
		global $wpdb;
		$table = $wpdb->prefix . 'fr_referrers';

		do {
			$code = strtoupper( bin2hex( random_bytes( 5 ) ) );
			$found = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE referral_code = %s", $code ) );
		} while ( $found );

		return $code;
	}
}
