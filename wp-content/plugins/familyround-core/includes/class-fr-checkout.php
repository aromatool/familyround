<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Câmpul opțional de cod de recomandare la checkout — are prioritate față de
 * cookie-ul de atribuire, dacă e completat manual.
 */
class FR_Checkout {

	public static function init() {
		add_action( 'woocommerce_after_order_notes', array( __CLASS__, 'render_referral_field' ) );
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'save_referral_code' ), 10, 2 );
	}

	public static function render_referral_field( $checkout ) {
		$cookie_code = isset( $_COOKIE[ FR_Referrals::COOKIE_NAME ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ FR_Referrals::COOKIE_NAME ] ) ) : '';

		echo '<div id="fr-referral-field">';
		woocommerce_form_field(
			'fr_referral_code',
			array(
				'type'        => 'text',
				'class'       => array( 'form-row-wide' ),
				'label'       => 'Cod de recomandare',
				'required'    => false,
			),
			$checkout->get_value( 'fr_referral_code' ) ?: $cookie_code
		);
		echo '</div>';
	}

	public static function save_referral_code( $order, $data ) {
		$posted = isset( $_POST['fr_referral_code'] ) ? sanitize_text_field( wp_unslash( $_POST['fr_referral_code'] ) ) : '';
		$cookie = isset( $_COOKIE[ FR_Referrals::COOKIE_NAME ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ FR_Referrals::COOKIE_NAME ] ) ) : '';

		$code = $posted ?: $cookie;

		if ( $code ) {
			$order->update_meta_data( '_fr_referral_code', strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $code ) ) );
		}
	}
}
