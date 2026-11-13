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
		$map = add_query_arg( 'code', $referrer->referral_code, home_url( '/harta-familyround/' ) );
		$wants_updates = rest_sanitize_boolean( $request->get_param( 'updates_opt_in' ) );

		$updates_note = $wants_updates
			? '<p style="margin:16px 0 0;color:#4c6253;font-size:13px;line-height:1.6;font-family: Arial, Helvetica, sans-serif;">Ai cerut și actualizări despre recomandări. Vei primi cel mult un email pe săptămână, numai când apar comenzi noi validate, cu link de dezabonare.</p>'
			: '';
		$html_body = '<div style="font-family: Arial, Helvetica, sans-serif; max-width: 560px; margin: 0 auto;">'
			. '<div style="padding: 34px 32px; background-color: #102f23; text-align: center;">'
			. '<img src="' . esc_url( get_theme_file_uri( 'assets/images/logo-icon.png' ) ) . '" width="44" height="42" alt="" style="display: block; margin: 0 auto 14px;">'
			. '<p style="margin: 0; color: #d4b77d; font-size: 12px; font-weight: 700; letter-spacing: 1.5px;">FAMILYROUND</p>'
			. '<p style="margin: 10px 0 0; color: #fffaf0; font-family: Georgia, \'Times New Roman\', serif; font-size: 27px; line-height: 1.3;">Bucuria poate merge mai departe cu tine.</p>'
			. '<p style="margin: 8px 0 0; color: #d8cab0; font-size: 14px;">Salut, ' . esc_html( $first_name ) . '! Linkul tău personal este gata.</p>'
			. '</div>'
			. '<div style="padding: 30px 32px; background-color: #f5efe3; color: #102f23;">'
			. '<p style="margin: 0 0 24px; text-align: center; font-size: 15px; line-height: 1.65;">Îți mulțumim că vrei să duci FamilyRound și către alte familii. Nu trebuie să cumperi un joc pentru a face parte din povestea noastră.</p>'
			. '<div style="margin: 0 0 24px; padding: 24px; background-color: #fdfbf5; border: 1px solid #ecdfc0; border-radius: 14px; text-align: center;">'
			. '<p style="margin: 0 0 8px; font-size: 12px; font-weight: 700; letter-spacing: .04em; color: #8a6d3b;">FIECARE COMANDĂ PRIN LINKUL TĂU</p>'
			. '<p style="margin: 0 0 8px; font-size: 40px; font-weight: 700; line-height: 1;">10 lei</p>'
			. '<p style="margin: 0; font-size: 14px; color: #4c6253;">ajung în Pușculița FamilyRound, fără cost suplimentar pentru familia care cumpără.</p>'
			. '</div>'
			. '<p style="margin: 0 0 7px; font-size: 12px; font-weight: 700; letter-spacing: .04em;">LINKUL TĂU PERSONAL</p>'
			. '<p style="margin: 0 0 18px; padding: 13px 16px; background-color: #fdfbf5; border: 1px solid #ecdfc0; border-radius: 8px; overflow-wrap: anywhere; font-size: 14px; font-weight: 700;">' . esc_html( $link ) . '</p>'
			. '<p style="margin: 0 0 10px;"><a href="' . esc_url( $stats ) . '" style="display: block; padding: 16px; background-color: #102f23; color: #fffaf0; text-decoration: none; font-weight: 700; border-radius: 10px; text-align: center;">Dă linkul mai departe</a></p>'
			. '<p style="margin: 0 0 28px; text-align: center; font-size: 13px; color: #4c6253;">Trimite-l pe WhatsApp, Facebook sau oricui crezi că i-ar plăcea jocul.</p>'
			. '<table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 0 24px;"><tr>'
			. '<td width="56" valign="top"><table role="presentation" cellpadding="0" cellspacing="0" style="background-color: #e9efe2; border-radius: 999px;"><tr><td width="48" height="48" align="center" valign="middle" style="font-size: 22px; line-height: 48px;">⚓</td></tr></table></td>'
			. '<td valign="top" style="padding-left: 14px;"><p style="margin: 0 0 4px; font-size: 16px; font-weight: 700;">Urmărește povestea linkului tău</p>'
			. '<p style="margin: 0 0 8px; font-size: 14px; line-height: 1.6; color: #4c6253;">Intră oricând pe site și descoperă pe hartă orașele în care au fost cumpărate jocuri prin recomandarea ta.</p>'
			. '<a href="' . esc_url( $map ) . '" style="color: #102f23; font-weight: 700; font-size: 14px;">Vezi harta și impactul tău →</a></td>'
			. '</tr></table>'
			. '<div style="margin: 0 0 24px; padding: 20px; background-color: #ece4cf; border-radius: 12px; text-align: center;">'
			. '<p style="margin: 0 0 6px; font-size: 16px; font-weight: 700;">Un link. Mai multe familii. Mai mult bine.</p>'
			. '<p style="margin: 0; font-size: 13px; font-style: italic; color: #4c6253;">Fiecare recomandare poate deveni începutul unor amintiri frumoase.</p>'
			. '</div>'
			. '<p style="margin: 0 0 18px; text-align: center; font-style: italic; font-size: 14px;">Cu drag, Alexandru &amp; Alexandra</p>'
			. $updates_note
			. '<p style="margin: 20px 0 0; padding-top: 16px; border-top: 1px solid #d8cab0; color: #4c6253; font-size: 13px; line-height: 1.6; text-align: center;">Urmărește-ne și pe <a href="https://www.facebook.com/familyround.ro" style="color: #102f23; font-weight: 700;">Facebook</a> și <a href="https://www.instagram.com/familyround.ro" style="color: #102f23; font-weight: 700;">Instagram</a> — povești noi de familie, în fiecare săptămână.</p>'
			. '</div></div>';

		// The link is already created and real — never withhold it just because the
		// welcome email had a transient delivery problem. Log it and tell the truth
		// in the response instead of blocking the whole signup on it.
		$mail_sent = wp_mail(
			$email,
			'Codul tău FamilyRound, de păstrat și de dat mai departe',
			$html_body,
			array( 'Content-Type: text/html; charset=UTF-8' )
		);
		if ( ! $mail_sent ) {
			error_log( 'FR_Referrals::handle_signup — welcome email failed for ' . $email );
		}
		if ( $wants_updates ) {
			self::opt_in_email( $first_name, $email );
		}
		return new WP_REST_Response(
			array(
				'code'       => $referrer->referral_code,
				'link'       => $link,
				'email_sent' => (bool) $mail_sent,
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
				'status'        => FR_Orders::CONFIRMED_STATUSES,
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
