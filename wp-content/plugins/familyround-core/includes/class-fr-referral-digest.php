<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/** A weekly progress note, sent only to people who opted in explicitly. */
class FR_Referral_Digest {
	const HOOK = 'fr_referral_digest_daily';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'schedule' ) );
		add_action( self::HOOK, array( __CLASS__, 'send_due' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_unsubscribe' ) );
	}

	public static function schedule() {
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::HOOK );
		}
	}

	private static function signature( $referrer ) {
		return hash_hmac( 'sha256', $referrer->id . '|' . strtolower( $referrer->email ), wp_salt( 'auth' ) );
	}

	public static function unsubscribe_url( $referrer ) {
		return add_query_arg( 'fr_updates', $referrer->id . '.' . self::signature( $referrer ), home_url( '/da-timpul-mai-departe/' ) );
	}

	private static function signed_referrer( $signed_value ) {
		$parts = explode( '.', (string) $signed_value, 2 );
		if ( 2 !== count( $parts ) || ! ctype_digit( $parts[0] ) || ! preg_match( '/^[a-f0-9]{64}$/', $parts[1] ) ) return null;
		global $wpdb;
		$referrer = $wpdb->get_row( $wpdb->prepare( "SELECT id, email FROM {$wpdb->prefix}fr_referrers WHERE id = %d", (int) $parts[0] ) );
		return $referrer && hash_equals( self::signature( $referrer ), $parts[1] ) ? $referrer : null;
	}

	public static function valid_unsubscribe_request() {
		if ( empty( $_GET['fr_updates'] ) ) return false;
		return (bool) self::signed_referrer( sanitize_text_field( wp_unslash( $_GET['fr_updates'] ) ) );
	}

	public static function handle_unsubscribe() {
		if ( ! is_page( 'da-timpul-mai-departe' ) || 'POST' !== $_SERVER['REQUEST_METHOD'] || empty( $_POST['fr_updates_unsubscribe'] ) ) return;
		$signed = sanitize_text_field( wp_unslash( $_POST['fr_updates_unsubscribe'] ) );
		$referrer = self::signed_referrer( $signed );
		if ( ! $referrer || ! isset( $_POST['fr_updates_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fr_updates_nonce'] ) ), 'fr_updates_unsubscribe' ) ) {
			wp_die( 'Linkul de dezabonare nu este valid.', 'FamilyRound', array( 'response' => 400 ) );
		}
		global $wpdb;
		$wpdb->update( $wpdb->prefix . 'fr_referrers', array( 'updates_opt_in' => 0 ), array( 'id' => $referrer->id ) );
		wp_safe_redirect( add_query_arg( 'fr_updates_done', '1', home_url( '/da-timpul-mai-departe/' ) ) );
		exit;
	}

	public static function send_due() {
		global $wpdb;
		$referrers = $wpdb->get_results( "SELECT id, first_name, email, referral_code, updates_consent_at, digest_count, digest_bonus, digest_last_sent_at FROM {$wpdb->prefix}fr_referrers WHERE updates_opt_in = 1" );
		$now = current_time( 'timestamp' );
		foreach ( $referrers as $referrer ) {
			$last = $referrer->digest_last_sent_at ?: $referrer->updates_consent_at;
			if ( $last && $now - strtotime( $last ) < 7 * DAY_IN_SECONDS ) continue;
			$impact = $wpdb->get_row( $wpdb->prepare(
				"SELECT COUNT(*) AS orders, COALESCE(SUM(bonus_amount), 0) AS bonus FROM {$wpdb->prefix}fr_referral_orders WHERE referrer_id = %d AND status = 'valid'",
				$referrer->id
			) );
			$count = (int) $impact->orders;
			$bonus = (float) $impact->bonus;
			$new_orders = $count - (int) $referrer->digest_count;
			$new_bonus = $bonus - (float) $referrer->digest_bonus;
			if ( $new_orders <= 0 || $new_bonus <= 0 ) {
				if ( $new_orders < 0 || $new_bonus < 0 ) {
					$wpdb->update( $wpdb->prefix . 'fr_referrers', array( 'digest_count' => $count, 'digest_bonus' => $bonus ), array( 'id' => $referrer->id ) );
				}
				continue;
			}
			$stats = FR_Referrals::public_stats_link( $referrer->referral_code );
			$map = add_query_arg( 'code', $referrer->referral_code, home_url( '/harta-familyround/' ) );
			$share = FR_Referrals::referral_link( $referrer->referral_code );
			$unsubscribe = self::unsubscribe_url( $referrer );
			$amount = number_format_i18n( $new_bonus, 0 );
			$body = '<div style="max-width:600px;margin:auto;padding:36px;background:#fffaf0;color:#173b2b;font-family:Arial,sans-serif;line-height:1.6">'
				. '<p style="color:#a07532;letter-spacing:2px;font-size:12px;font-weight:bold">FAMILYROUND · CERCUL TĂU</p>'
				. '<h1 style="font:normal 32px Georgia,serif">Încă un pas dat mai departe.</h1>'
				. '<p>Salut, ' . esc_html( $referrer->first_name ) . '! Prin codul tău s-au confirmat încă <strong>' . esc_html( $new_orders ) . ' ' . ( 1 === $new_orders ? 'comandă' : 'comenzi' ) . '</strong>. FamilyRound a pus deoparte încă <strong>' . esc_html( $amount ) . ' lei</strong> în Pușculiță.</p>'
				. '<p>Sumele sunt alocate de noi; transferurile către cauze sunt prezentate separat.</p>'
				. '<p><a href="' . esc_url( $stats ) . '" style="display:inline-block;background:#173b2b;color:#fffaf0;padding:12px 18px;text-decoration:none">Vezi impactul tău →</a></p>'
				. '<p><a href="' . esc_url( $map ) . '">Vezi pe hartă unde au fost cumpărate jocurile</a><br><a href="' . esc_url( $share ) . '">Trimite mai departe linkul tău</a></p>'
				. '<p style="border-top:1px solid #d8cab0;padding-top:16px;font-size:12px;color:#647667">Primești acest mesaj pentru că ai cerut actualizări despre recomandări. <a href="' . esc_url( $unsubscribe ) . '">Dezabonează-te</a>.</p></div>';
			if ( wp_mail( $referrer->email, 'Noutăți din cercul tău FamilyRound', $body, array( 'Content-Type: text/html; charset=UTF-8' ) ) ) {
				$wpdb->update( $wpdb->prefix . 'fr_referrers', array( 'digest_count' => $count, 'digest_bonus' => $bonus, 'digest_last_sent_at' => current_time( 'mysql' ) ), array( 'id' => $referrer->id ) );
			}
		}
	}
}
