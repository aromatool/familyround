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
			$body = '<div style="max-width:560px;margin:auto;font-family:Arial,Helvetica,sans-serif;">'
				. '<div style="padding:34px 32px;background-color:#102f23;text-align:center;">'
				. '<img src="' . esc_url( get_theme_file_uri( 'assets/images/logo-icon.png' ) ) . '" width="44" height="42" alt="" style="display:block;margin:0 auto 14px;">'
				. '<p style="margin:0;color:#d4b77d;font-size:12px;font-weight:700;letter-spacing:1.5px;">FAMILYROUND · CERCUL TĂU</p>'
				. '<p style="margin:10px 0 0;color:#fffaf0;font-family:Georgia,\'Times New Roman\',serif;font-size:27px;line-height:1.25;">Încă un pas dat mai departe.</p>'
				. '</div>'
				. '<div style="padding:30px 32px;background-color:#f5efe3;color:#102f23;">'
				. '<div style="margin:0 0 20px;padding:28px;background-color:#fdfbf5;border:1px solid #ecdfc0;border-radius:14px;">'
				. '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 18px;"><tr>'
				. '<td width="56" valign="middle"><table role="presentation" cellpadding="0" cellspacing="0" style="background-color:#f3cf7e;border-radius:999px;"><tr><td width="48" height="48" align="center" valign="middle" style="font-size:24px;line-height:48px;">🪙</td></tr></table></td>'
				. '<td valign="middle" style="padding-left:14px;font-size:15px;line-height:1.6;">Salut, ' . esc_html( $referrer->first_name ) . '! Prin codul tău s-au confirmat încă <strong>' . esc_html( $new_orders ) . ' ' . ( 1 === $new_orders ? 'comandă' : 'comenzi' ) . '</strong>. FamilyRound a pus deoparte încă <strong style="background-color:#f3cf7e;padding:1px 8px;border-radius:6px;">' . esc_html( $amount ) . ' lei</strong> în Pușculiță.</td>'
				. '</tr></table>'
				. '<p style="margin:0 0 22px;font-size:13px;color:#4c6253;">Sumele sunt alocate de noi; transferurile către cauze sunt prezentate separat.</p>'
				. '<p style="margin:0 0 10px;text-align:center;"><a href="' . esc_url( $stats ) . '" style="display:inline-block;padding:13px 26px;background-color:#102f23;color:#fffaf0;text-decoration:none;font-weight:700;border-radius:8px;">Vezi impactul tău →</a></p>'
				. '<p style="margin:0;text-align:center;font-size:13px;"><a href="' . esc_url( $map ) . '" style="color:#102f23;font-weight:600;">Vezi pe hartă unde au fost cumpărate jocurile</a> · <a href="' . esc_url( $share ) . '" style="color:#102f23;font-weight:600;">Trimite mai departe linkul tău</a></p>'
				. '</div>'
				. '<p style="margin:0;padding-top:16px;border-top:1px solid #d8cab0;font-size:12px;color:#647667;">Primești acest mesaj pentru că ai cerut actualizări despre recomandări. <a href="' . esc_url( $unsubscribe ) . '" style="color:#647667;">Dezabonează-te</a>.</p>'
				. '</div></div>';
			if ( wp_mail( $referrer->email, 'Noutăți din cercul tău FamilyRound', $body, array( 'Content-Type: text/html; charset=UTF-8' ) ) ) {
				$wpdb->update( $wpdb->prefix . 'fr_referrers', array( 'digest_count' => $count, 'digest_bonus' => $bonus, 'digest_last_sent_at' => current_time( 'mysql' ) ), array( 'id' => $referrer->id ) );
			}
		}
	}
}
