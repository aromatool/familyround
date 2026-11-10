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
		add_action( 'template_redirect', array( __CLASS__, 'save_cart_referral_code' ) );
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'save_referral_code' ), 10, 2 );
		add_action( 'woocommerce_after_checkout_validation', array( __CLASS__, 'validate_referral_code' ), 10, 2 );
		add_action( 'woocommerce_after_checkout_validation', array( __CLASS__, 'validate_invoice_details' ), 20, 2 );
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'save_invoice_details' ), 40, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'snapshot_game_eligibility' ), 10, 4 );
		add_action( 'woocommerce_cart_totals_after_order_total', array( __CLASS__, 'render_piggybank_preview' ) );
		add_action( 'woocommerce_cart_totals_after_order_total', array( __CLASS__, 'render_cart_shipping_note' ), 20 );
		add_action( 'woocommerce_review_order_after_order_total', array( __CLASS__, 'render_piggybank_preview' ) );
		add_action( 'woocommerce_proceed_to_checkout', array( __CLASS__, 'render_cart_referral_field' ), 5 );
		add_filter( 'woocommerce_checkout_cart_item_quantity', array( __CLASS__, 'render_checkout_quantity' ), 10, 3 );
		add_filter( 'woocommerce_checkout_registration_enabled', '__return_false' );
		add_filter( 'woocommerce_checkout_registration_required', '__return_false' );
		add_filter( 'woocommerce_checkout_fields', array( __CLASS__, 'prioritize_contact_fields' ), 999 );
		add_filter( 'woocommerce_checkout_fields', array( __CLASS__, 'relax_address_fields_for_pickup' ), 1000 );
		add_filter( 'woocommerce_checkout_get_value', array( __CLASS__, 'checkout_always_starts_blank' ), 10, 2 );
		add_action( 'woocommerce_checkout_process', array( __CLASS__, 'default_country_to_ro' ) );
		add_filter( 'woocommerce_checkout_required_field_notice', array( __CLASS__, 'strip_fieldset_prefix' ), 10, 3 );
		add_filter( 'woocommerce_package_rates', array( __CLASS__, 'hide_unavailable_locker_rates' ), 100 );
		add_filter( 'woocommerce_shipping_package_name', array( __CLASS__, 'shipping_package_name' ) );
		add_action( 'wp_ajax_fr_check_referral_code', array( __CLASS__, 'ajax_check_referral_code' ) );
		add_action( 'wp_ajax_nopriv_fr_check_referral_code', array( __CLASS__, 'ajax_check_referral_code' ) );
		add_action( 'wp_ajax_fr_checkout_quantity', array( __CLASS__, 'ajax_checkout_quantity' ) );
		add_action( 'wp_ajax_nopriv_fr_checkout_quantity', array( __CLASS__, 'ajax_checkout_quantity' ) );

		// "cod" now covers two different real situations — cash at pickup, and
		// ramburs paid to the courier — so its title/description must read
		// right for whichever one the customer actually chose.
		add_filter( 'woocommerce_gateway_title', array( __CLASS__, 'cod_title_for_context' ), 10, 2 );
		add_filter( 'woocommerce_gateway_description', array( __CLASS__, 'cod_description_for_context' ), 10, 2 );
		// Courier COD costs FamilyRound a real per-order fee the carrier
		// charges for collecting cash — recovered as a flat line item rather
		// than attempting to mirror Colete Online's own live rate calculation
		// (see commit message / conversation: too fragile to reimplement).
		add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'add_cod_courier_fee' ) );
		// WC's own "chosen_shipping_methods" session value is an unreliable
		// pickup/delivery signal: before a full address exists, local_pickup
		// can be the only available rate and gets auto-selected even though
		// the "Livrare" toggle is active. checkout.js posts its own
		// fr_shipping_mode field with every update_checkout call instead —
		// trust that. The toggle always starts on "Livrare" on a fresh page
		// load, so reset to match at the start of every checkout page view.
		add_action( 'woocommerce_before_checkout_form', array( __CLASS__, 'reset_shipping_mode_session' ) );
		add_action( 'woocommerce_checkout_update_order_review', array( __CLASS__, 'capture_shipping_mode_from_ajax' ) );
	}

	public static function reset_shipping_mode_session() {
		if ( function_exists( 'WC' ) && WC()->session ) {
			WC()->session->set( 'fr_shipping_mode', 'delivery' );
		}
	}

	public static function capture_shipping_mode_from_ajax( $post_data ) {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return;
		}
		parse_str( $post_data, $data );
		if ( isset( $data['fr_shipping_mode'] ) ) {
			WC()->session->set( 'fr_shipping_mode', 'pickup' === $data['fr_shipping_mode'] ? 'pickup' : 'delivery' );
		}
	}

	const COD_COURIER_FEE = 3.00;

	private static function is_pickup_session() {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return false;
		}
		return 'pickup' === WC()->session->get( 'fr_shipping_mode', 'delivery' );
	}

	public static function cod_title_for_context( $title, $gateway_id ) {
		if ( 'cod' !== $gateway_id || is_admin() ) {
			return $title;
		}
		return self::is_pickup_session() ? 'Plată la ridicare' : sprintf( 'Ramburs la curier (+%s lei)', number_format( self::COD_COURIER_FEE, 0 ) );
	}

	public static function cod_description_for_context( $description, $gateway_id ) {
		if ( 'cod' !== $gateway_id || is_admin() ) {
			return $description;
		}
		return self::is_pickup_session()
			? 'Plătești cash la ridicare.'
			: sprintf( 'Plătești cash curierului la livrare. Se adaugă o taxă de %s lei pentru colectare ramburs.', number_format( self::COD_COURIER_FEE, 0 ) );
	}

	public static function add_cod_courier_fee( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}
		if ( self::is_pickup_session() ) {
			return; // Cash at pickup has no courier involved — no fee.
		}
		if ( ! function_exists( 'WC' ) || 'cod' !== WC()->session->get( 'chosen_payment_method' ) ) {
			return;
		}
		$cart->add_fee( 'Taxă ramburs', self::COD_COURIER_FEE, false );
	}

	public static function prioritize_contact_fields( $fields ) {
		if ( isset( $fields['order']['order_comments'] ) ) {
			$fields['order']['order_comments']['label'] = 'Observații';
			$fields['order']['order_comments']['placeholder'] = '';
		}
		// Fără CUI, nu putem factura firme — ascundem opțiunea "Firmă" până
		// se finalizează înregistrarea; eliminăm odată cu FR_PAYMENT_AWAITING_CUI.
		$hide_company_invoicing = function_exists( 'fr_payment_awaiting_cui' ) && fr_payment_awaiting_cui();
		if ( ! $hide_company_invoicing && ! isset( $fields['billing']['smartbill_billing_type'] ) ) {
			$fields['billing']['smartbill_billing_type'] = array(
				'type'     => 'select',
				'label'    => 'Factura este pentru',
				'options'  => array( 'pf' => 'Persoană fizică', 'pj' => 'Firmă' ),
				'class'    => array( 'form-row-wide' ),
				'priority' => 42,
				'required' => true,
			);
		}
		if ( ! $hide_company_invoicing && ! isset( $fields['billing']['smartbill_billing_cif'] ) ) {
			$fields['billing']['smartbill_billing_cif'] = array(
				'type'     => 'text',
				'label'    => 'CUI / CIF',
				'class'    => array( 'form-row-wide' ),
				'priority' => 46,
				'required' => false,
			);
		}
		if ( ! $hide_company_invoicing && ! isset( $fields['billing']['smartbill_billing_company_name'] ) ) {
			$fields['billing']['smartbill_billing_company_name'] = array(
				'type'     => 'text',
				'label'    => 'Denumirea firmei',
				'class'    => array( 'form-row-wide' ),
				'priority' => 92,
				'required' => false,
			);
		}
		if ( ! $hide_company_invoicing && ! isset( $fields['billing']['smartbill_billing_nr_reg_com'] ) ) {
			$fields['billing']['smartbill_billing_nr_reg_com'] = array(
				'type'     => 'text',
				'label'    => 'Număr registrul comerțului',
				'class'    => array( 'form-row-wide' ),
				'priority' => 94,
				'required' => false,
			);
		}
		$priority = array(
			'billing_last_name'  => 10,
			'billing_first_name' => 20,
			'billing_email'      => 30,
			'billing_phone'      => 40,
			'billing_country'    => 50,
			'billing_city_state' => 60,
			'billing_locality'   => 60,
			'billing_state'      => 61,
			'billing_city'       => 62,
			'billing_postcode'   => 65,
			'billing_street'     => 70,
			'billing_street_number' => 71,
			'billing_address_1'  => 72,
			'billing_address_2'  => 80,
			'smartbill_billing_type' => 90,
			'billing_company'    => 91,
			'smartbill_billing_company_name' => 92,
			'smartbill_billing_cif' => 93,
			'smartbill_billing_nr_reg_com' => 94,
		);
		foreach ( $priority as $key => $value ) {
			if ( isset( $fields['billing'][ $key ] ) ) {
				$fields['billing'][ $key ]['priority'] = $value;
			}
		}
		if ( isset( $fields['billing']['billing_company'] ) ) {
			$fields['billing']['billing_company']['label'] = 'Denumirea firmei';
			$fields['billing']['billing_company']['required'] = false;
		}
		if ( isset( $fields['billing']['billing_phone'] ) ) {
			$fields['billing']['billing_phone']['required'] = true;
		}
		foreach ( array( 'smartbill_billing_cif', 'smartbill_billing_company_name', 'smartbill_billing_nr_reg_com' ) as $company_field ) {
			if ( isset( $fields['billing'][ $company_field ] ) ) {
				$fields['billing'][ $company_field ]['required'] = false;
			}
		}
		if ( isset( $fields['billing']['billing_address_1'] ) ) {
			$fields['billing']['billing_address_1']['label'] = 'Stradă și număr';
		}
		if ( isset( $fields['billing']['billing_city'] ) ) {
			$fields['billing']['billing_city']['label'] = 'Localitate';
		}
		$labels = array(
			'billing_last_name'  => 'Nume',
			'billing_first_name' => 'Prenume',
			'billing_email'      => 'Adresă de email',
			'billing_phone'      => 'Telefon',
			'billing_country'    => 'Țară',
			'billing_state'      => 'Județ',
			'billing_city_state' => 'Localitate și județ',
			'billing_postcode'   => 'Cod poștal',
			'billing_street'     => 'Stradă',
			'billing_street_number' => 'Număr',
			'billing_address_2'  => 'Bloc, scară, apartament',
		);
		foreach ( $labels as $key => $label ) {
			if ( isset( $fields['billing'][ $key ] ) ) {
				$fields['billing'][ $key ]['label'] = $label;
			}
		}
		return $fields;
	}


	/**
	 * WooCommerce prefills billing/shipping fields from the logged-in
	 * customer's account or saved session data. Fine for a repeat shopper,
	 * but this checkout gets reused constantly for testing with the same
	 * admin account, and every visit inherited whatever address was typed
	 * last time. Always start blank; a failed submission still keeps what
	 * was just typed ($_POST wins earlier in WC_Checkout::get_value(),
	 * before this filter runs).
	 */
	public static function checkout_always_starts_blank( $value, $key ) {
		// Country isn't "leftover personal data" — this store only ships
		// within Romania, and blanking it broke WooCommerce's own shippable-cart
		// validation (see default_country_to_ro() below).
		if ( 'billing_country' === $key || 'shipping_country' === $key ) {
			return $value;
		}
		foreach ( array( 'billing_', 'shipping_', 'smartbill_' ) as $prefix ) {
			if ( 0 === strpos( $key, $prefix ) ) {
				return '';
			}
		}
		return $value;
	}

	/**
	 * WooCommerce's own validate_checkout() rejects ANY shippable cart —
	 * "Ridicare personală" included, since it still counts as "needs shipping"
	 * — with "Please enter an address to continue." unless a shipping country
	 * is set. Pickup hides the whole address block (country included), so a
	 * fresh session with nothing left over from a previous visit can genuinely
	 * reach checkout with no country set. Default it to the only country this
	 * store serves, before WC's own validation runs.
	 */
	public static function default_country_to_ro() {
		if ( ! WC()->customer ) {
			return;
		}
		if ( ! WC()->customer->get_billing_country() ) {
			WC()->customer->set_billing_country( 'RO' );
		}
		if ( ! WC()->customer->get_shipping_country() ) {
			WC()->customer->set_shipping_country( 'RO' );
		}
	}

	/**
	 * The address block is hidden client-side when the customer picks
	 * "Ridicare personală" (see assets/checkout.js, initShippingModeToggle) —
	 * no address is needed to pick up in person. Relax the same fields here
	 * too, so a pickup order can actually submit with them empty; the JS hiding
	 * alone wouldn't stop server-side required-field validation from blocking
	 * the order. Defaults to required (today's behaviour) whenever the chosen
	 * shipping method isn't known to be pickup, so a broken/disabled toggle
	 * fails safe instead of silently allowing a delivery order with no address.
	 */
	public static function relax_address_fields_for_pickup( $fields ) {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return $fields;
		}
		$chosen = WC()->session->get( 'chosen_shipping_methods' );
		$is_pickup = is_array( $chosen ) && ! empty( $chosen[0] ) && 0 === strpos( $chosen[0], 'local_pickup' );
		if ( ! $is_pickup ) {
			return $fields;
		}
		$address_keys = array( 'billing_country', 'billing_city_state', 'billing_locality', 'billing_state', 'billing_city', 'billing_postcode', 'billing_street', 'billing_street_number', 'billing_address_1' );
		foreach ( $address_keys as $key ) {
			if ( isset( $fields['billing'][ $key ] ) ) {
				$fields['billing'][ $key ]['required'] = false;
			}
		}
		return $fields;
	}

	/**
	 * Colete Online can return a locker quote without any nearby pickup point
	 * when its map option is enabled. Such a quote cannot be selected reliably.
	 * Keep the plugin's prices, carrier ordering and point picker unchanged.
	 */
	public static function hide_unavailable_locker_rates( $rates ) {
		foreach ( $rates as $key => $rate ) {
			if ( ! is_object( $rate ) || 'coleteonline' !== $rate->get_method_id() ) {
				continue;
			}
			$meta = $rate->get_meta_data();
			if ( array_key_exists( 'shipping_points_list', $meta ) && empty( $meta['shipping_points_list'] ) ) {
				unset( $rates[ $key ] );
			}
		}
		return $rates;
	}

	/**
	 * WooCommerce prefixes required-field notices with "Billing "/"Shipping "
	 * (translated "Facturare "/"Livrare "), which reads redundantly on a
	 * single-address checkout. Strip it back to the plain field label.
	 */
	public static function strip_fieldset_prefix( $message, $field_label, $key ) {
		$clean_label = preg_replace( '/^(Facturare|Livrare|Billing|Shipping)\s+/u', '', $field_label );
		return sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . esc_html( $clean_label ) . '</strong>' );
	}

	public static function validate_invoice_details( $data, $errors ) {
		$type = isset( $data['smartbill_billing_type'] ) ? $data['smartbill_billing_type'] : 'pf';
		if ( 'pj' !== $type ) {
			return;
		}
		$company = ! empty( $data['smartbill_billing_company_name'] ) ? $data['smartbill_billing_company_name'] : ( $data['billing_company'] ?? '' );
		if ( ! trim( $company ) ) {
			$errors->add( 'fr_company_required', 'Completează denumirea firmei pentru factură.' );
		}
		if ( empty( $data['smartbill_billing_cif'] ) ) {
			$errors->add( 'fr_cif_required', 'Completează CUI / CIF pentru factură.' );
		}
	}

	public static function save_invoice_details( $order, $data ) {
		$type = isset( $data['smartbill_billing_type'] ) && 'pj' === $data['smartbill_billing_type'] ? 'pj' : 'pf';
		$order->update_meta_data( 'smartbill_billing_type', $type );
		if ( 'pj' === $type ) {
			$company = ! empty( $data['smartbill_billing_company_name'] ) ? $data['smartbill_billing_company_name'] : ( $data['billing_company'] ?? '' );
			$order->set_billing_company( sanitize_text_field( $company ) );
			$order->update_meta_data( 'smartbill_billing_company_name', sanitize_text_field( $company ) );
			$order->update_meta_data( 'smartbill_billing_cif', sanitize_text_field( $data['smartbill_billing_cif'] ?? '' ) );
			$order->update_meta_data( 'smartbill_billing_nr_reg_com', sanitize_text_field( $data['smartbill_billing_nr_reg_com'] ?? '' ) );
		} else {
			$order->set_billing_company( '' );
		}
	}

	public static function shipping_package_name( $name ) {
		if ( function_exists( 'is_cart' ) && is_cart() ) {
			return 'Livrare';
		}
		return function_exists( 'is_checkout' ) && is_checkout() ? 'Livrare' : $name;
	}

	public static function render_cart_shipping_note() {
		echo '<tr class="fr-shipping-estimate-note"><td colspan="2">Tariful livrării apare la pasul următor, după ce completezi destinația.</td></tr>';
	}

	public static function render_checkout_quantity( $html, $cart_item, $cart_item_key ) {
		$product = $cart_item['data'] ?? null;
		if ( ! $product || $product->is_sold_individually() ) {
			return $html;
		}
		$quantity = (int) $cart_item['quantity'];
		$maximum = (int) $product->get_max_purchase_quantity();
		return '<span class="fr-checkout-quantity" aria-label="Cantitate">'
			. '<button type="button" class="fr-quantity-step" data-step="-1" aria-label="Scade cantitatea"' . ( $quantity <= 1 ? ' disabled' : '' ) . '>−</button>'
			. '<input type="number" class="fr-quantity-input" data-cart-key="' . esc_attr( $cart_item_key ) . '" min="1"' . ( $maximum > 0 ? ' max="' . esc_attr( $maximum ) . '"' : '' ) . ' value="' . esc_attr( $quantity ) . '" aria-label="Cantitate joc" inputmode="numeric">'
			. '<button type="button" class="fr-quantity-step" data-step="1" aria-label="Crește cantitatea"' . ( $maximum > 0 && $quantity >= $maximum ? ' disabled' : '' ) . '>+</button>'
			. '</span>';
	}

	public static function ajax_checkout_quantity() {
		check_ajax_referer( 'fr_checkout_quantity', 'nonce' );
		if ( ! function_exists( 'WC' ) ) {
			wp_send_json_error( array( 'message' => 'Coșul nu este disponibil momentan.' ), 503 );
		}
		if ( ! WC()->cart && function_exists( 'wc_load_cart' ) ) {
			wc_load_cart();
		}
		$key = isset( $_POST['cart_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_key'] ) ) : '';
		$quantity = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 0;
		$item = WC()->cart ? WC()->cart->get_cart_item( $key ) : null;
		if ( ! $item || ! $quantity || empty( $item['data'] ) ) {
			wp_send_json_error( array( 'message' => 'Cantitatea nu a putut fi actualizată.' ), 400 );
		}
		$product = $item['data'];
		$maximum = (int) $product->get_max_purchase_quantity();
		if ( $product->is_sold_individually() || 0 === $maximum || ( $maximum > 0 && $quantity > $maximum ) || ! apply_filters( 'woocommerce_update_cart_validation', true, $key, $item, $quantity ) ) {
			wp_send_json_error( array( 'message' => 'Cantitatea aleasă nu este disponibilă pentru acest joc.' ), 400 );
		}
		WC()->cart->set_quantity( $key, $quantity, true );
		wp_send_json_success( array( 'quantity' => $quantity ) );
	}

	private static function current_referral_code() {
		if ( isset( $_POST['post_data'] ) && is_string( $_POST['post_data'] ) ) {
			parse_str( wp_unslash( $_POST['post_data'] ), $checkout_data );
			if ( array_key_exists( 'fr_referral_code', $checkout_data ) ) {
				return strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', sanitize_text_field( is_string( $checkout_data['fr_referral_code'] ) ? $checkout_data['fr_referral_code'] : '' ) ) );
			}
		}
		if ( isset( $_POST['fr_referral_code'] ) && is_string( $_POST['fr_referral_code'] ) ) {
			return strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', sanitize_text_field( wp_unslash( $_POST['fr_referral_code'] ) ) ) );
		}
		$has_manual = WC()->session && WC()->session->get( 'fr_referral_manual_set' );
		$source = $has_manual
			? (string) WC()->session->get( 'fr_referral_code', '' )
			: ( isset( $_COOKIE[ FR_Referrals::COOKIE_NAME ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ FR_Referrals::COOKIE_NAME ] ) ) : '' );
		return strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $source ) );
	}

	private static function referral_code_exists( $code ) {
		global $wpdb;
		return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}fr_referrers WHERE referral_code = %s", $code ) );
	}

	/**
	 * Single source of truth for "does this code actually earn the bonus for
	 * this buyer" — shared by the order-review preview and the checkout
	 * "Aplică" button, so the two never disagree about the same code.
	 */
	private static function referral_eligibility( $code, $email, $phone ) {
		$has_referral = $code && self::referral_code_exists( $code );
		$identity_complete = is_email( $email ) && '' !== trim( (string) $phone );
		global $wpdb;
		$referrer_email = $has_referral ? $wpdb->get_var( $wpdb->prepare(
			"SELECT email FROM {$wpdb->prefix}fr_referrers WHERE referral_code = %s", $code
		) ) : '';
		$repeat_buyer = $has_referral && $identity_complete && FR_Orders::has_prior_confirmed_order( $email, $phone );
		$self_referral = $has_referral && $identity_complete && 0 === strcasecmp( $email, (string) $referrer_email );
		return array(
			'has_referral'      => $has_referral,
			'identity_complete' => $identity_complete,
			'repeat_buyer'      => $repeat_buyer,
			'self_referral'     => $self_referral,
			// Shown optimistically as soon as the code is valid, not gated on
			// email/phone being filled in yet — only actually disqualified once
			// those reveal a repeat buyer or a self-referral.
			'bonus_eligible'    => $has_referral && ! $repeat_buyer && ! $self_referral,
		);
	}

	public static function ajax_check_referral_code() {
		check_ajax_referer( 'fr_referral_check', 'nonce' );
		$raw   = isset( $_POST['code'] ) && is_string( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
		$code  = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $raw ) );
		$email = isset( $_POST['email'] ) && is_string( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$phone = isset( $_POST['phone'] ) && is_string( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
		if ( '' === $code || strlen( $code ) > 20 ) {
			wp_send_json_success( array( 'valid' => false ) );
		}
		$eligibility = self::referral_eligibility( $code, $email, $phone );
		wp_send_json_success( array_merge( array( 'valid' => $eligibility['has_referral'] ), $eligibility ) );
	}

	public static function save_cart_referral_code() {
		if ( ! function_exists( 'is_cart' ) || ! is_cart() || ! isset( $_POST['fr_cart_referral_apply'] ) ) {
			return;
		}
		if ( ! isset( $_POST['fr_cart_referral_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fr_cart_referral_nonce'] ) ), 'fr_cart_referral' ) ) {
			wc_add_notice( 'Nu am putut verifica formularul. Încearcă din nou.', 'error' );
			wp_safe_redirect( wc_get_cart_url() );
			exit;
		}
		$raw = isset( $_POST['fr_cart_referral_code'] ) ? sanitize_text_field( wp_unslash( $_POST['fr_cart_referral_code'] ) ) : '';
		$code = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $raw ) );
		if ( $code && ! self::referral_code_exists( $code ) ) {
			wc_add_notice( 'Codul de recomandare nu a fost găsit. Verifică-l și încearcă din nou.', 'error' );
			wp_safe_redirect( wc_get_cart_url() );
			exit;
		}
		if ( WC()->session ) {
			WC()->session->set( 'fr_referral_manual_set', true );
			WC()->session->set( 'fr_referral_code', $code );
		}
		wc_add_notice( $code ? 'Cod valid. Dacă aceasta este prima ta comandă FamilyRound, noi mai adăugăm 5 lei pentru fiecare joc în Pușculiță.' : 'Codul introdus manual a fost eliminat.', 'success' );
		wp_safe_redirect( wc_get_cart_url() );
		exit;
	}

	public static function render_cart_referral_field() {
		$code = self::current_referral_code();
		$active = $code && self::referral_code_exists( $code );
		if ( $active ) {
			echo '<div class="fr-cart-referral-applied"><span class="fr-cart-referral-applied-mark" aria-hidden="true">✓</span><div><strong>Cod valid: ' . esc_html( $code ) . '</strong><p>Dacă aceasta este prima ta comandă FamilyRound, noi mai adăugăm 5 lei pentru fiecare joc în Pușculiță.</p></div></div>';
			echo '<details class="fr-cart-referral-change"><summary>Schimbă sau elimină codul</summary>';
		}
		echo '<form class="fr-cart-referral" method="post" action="' . esc_url( wc_get_cart_url() ) . '">';
		echo '<label for="fr-cart-referral-code">' . ( $active ? 'Alt cod de recomandare' : 'Ai un cod de recomandare?' ) . '</label>';
		if ( ! $active ) {
			echo '<p>Nu este un cupon de reducere. La prima ta comandă, un cod primit de la altcineva adaugă încă 5 lei pentru fiecare joc în Pușculiță.</p>';
		}
		echo '<div class="fr-cart-referral-controls"><input id="fr-cart-referral-code" name="fr_cart_referral_code" type="text" value="' . esc_attr( $code ) . '" maxlength="20" autocomplete="off" placeholder="Codul primit"><button type="submit" name="fr_cart_referral_apply" value="1">' . ( $active ? 'Salvează' : 'Aplică' ) . '</button></div>';
		if ( $active ) {
			echo '<p>Șterge codul din câmp și apasă „Salvează” dacă nu vrei să folosești o recomandare.</p>';
		}
		wp_nonce_field( 'fr_cart_referral', 'fr_cart_referral_nonce' );
		echo '</form>';
		if ( $active ) {
			echo '</details>';
		}
	}

	public static function snapshot_game_eligibility( $item, $cart_item_key, $values, $order ) {
		$eligible = FR_Orders::product_is_eligible( $values['data'] ?? null, $item, $order );
		$item->add_meta_data( '_fr_game_eligible', $eligible ? 'yes' : 'no', true );
	}

	public static function render_piggybank_preview() {
		if ( ! WC()->cart ) {
			return;
		}
		$units = 0;
		foreach ( WC()->cart->get_cart() as $line ) {
			if ( FR_Orders::product_is_eligible( $line['data'] ?? null ) ) {
				$units += (int) $line['quantity'];
			}
		}
		if ( ! $units ) {
			return;
		}
		$amount = $units * FR_Orders::SALE_AMOUNT;
		$code = self::current_referral_code();
		$has_referral = $code && self::referral_code_exists( $code );
		$checkout_data = array();
		if ( isset( $_POST['post_data'] ) && is_string( $_POST['post_data'] ) ) {
			parse_str( wp_unslash( $_POST['post_data'] ), $checkout_data );
		}
		$email = isset( $checkout_data['billing_email'] ) && is_string( $checkout_data['billing_email'] ) ? sanitize_email( $checkout_data['billing_email'] ) : '';
		$phone = isset( $checkout_data['billing_phone'] ) && is_string( $checkout_data['billing_phone'] ) ? sanitize_text_field( $checkout_data['billing_phone'] ) : '';
		if ( ! $checkout_data && function_exists( 'is_checkout' ) && is_checkout() && WC()->checkout() ) {
			$email = sanitize_email( (string) WC()->checkout()->get_value( 'billing_email' ) );
			$phone = sanitize_text_field( (string) WC()->checkout()->get_value( 'billing_phone' ) );
		}
		$eligibility = self::referral_eligibility( $code, $email, $phone );
		$identity_complete = $eligibility['identity_complete'];
		$repeat_buyer = $eligibility['repeat_buyer'];
		$self_referral = $eligibility['self_referral'];
		$bonus_eligible = $eligibility['bonus_eligible'];
		$bonus = $bonus_eligible ? $units * FR_Orders::BONUS_AMOUNT : 0;
		$total = $amount + $bonus;
		echo '<tr class="fr-purchase-impact"><td colspan="2"><div class="fr-impact-preview">';
		echo '<span class="fr-impact-eyebrow">Pușculița FamilyRound</span>';
		echo '<p class="fr-impact-title">Felicitări! Bucuria voastră merge mai departe.</p>';
		echo '<p class="fr-impact-amount"><strong>' . esc_html( number_format_i18n( $total, 0 ) ) . ' lei</strong><span>' . ( $bonus_eligible ? 'pot ajunge în Pușculiță din această comandă' : 'vor merge în Pușculiță din această comandă' ) . '</span></p>';
		if ( $bonus_eligible ) {
			echo '<p class="fr-impact-note">Ce frumos: fiind prima ta comandă FamilyRound, codul ' . esc_html( $code ) . ' dublează contribuția noastră pentru fiecare joc. Tu nu plătești nimic în plus.</p>';
		} elseif ( $has_referral && $identity_complete && $repeat_buyer ) {
			echo '<p class="fr-impact-note">Îți mulțumim că revii la FamilyRound. Bonusul de recomandare se acordă la prima comandă, iar comanda de acum contribuie în continuare la Pușculiță.</p>';
		} elseif ( $has_referral && $identity_complete && $self_referral ) {
			echo '<p class="fr-impact-note">Acesta este codul tău. Bonusul de recomandare se acordă unei alte familii, la prima ei comandă. Pentru jocurile tale punem 5 lei per joc în Pușculiță.</p>';
		} elseif ( $has_referral ) {
			echo '<p class="fr-impact-note">Cod valid: ' . esc_html( $code ) . '. Dacă aceasta este prima ta comandă FamilyRound, noi mai adăugăm 5 lei pentru fiecare joc în Pușculiță.</p>';
		} else {
			echo '<p class="fr-impact-note">Noi punem banii deoparte; voi nu plătiți nimic în plus.</p>';
		}
		echo '<a class="fr-impact-link" href="' . esc_url( home_url( '/pusculita-familyround/' ) ) . '">Vezi pentru ce strângem bani <span aria-hidden="true">↗</span></a>';
		echo '<div id="fr-referral-field" class="fr-impact-referral">';
		echo '<label for="fr_referral_code">Cod de recomandare <span class="optional">(opțional)</span></label>';
		echo '<div class="fr-impact-referral-controls">';
		echo '<input type="text" id="fr_referral_code" name="fr_referral_code" value="' . esc_attr( $code ) . '" maxlength="20" autocomplete="off" placeholder="Codul primit de la altcineva">';
		echo '<button type="button" id="fr_referral_apply">Aplică</button>';
		echo '</div>';
		echo '</div>';
		echo '</div></td></tr>';
	}


	public static function save_referral_code( $order, $data ) {
		$code = self::current_referral_code();

		if ( $code ) {
			$order->update_meta_data( '_fr_referral_code', $code );
		}
		if ( WC()->session ) {
			WC()->session->set( 'fr_referral_manual_set', null );
			WC()->session->set( 'fr_referral_code', null );
		}
	}

	public static function validate_referral_code( $data, $errors ) {
		$code = self::current_referral_code();
		if ( $code && ! self::referral_code_exists( $code ) ) {
			$errors->add( 'fr_referral_invalid', 'Codul de recomandare nu a fost găsit. Verifică-l înainte de a finaliza comanda.' );
		}
	}

	// Payment-method gating by shipping (cash only for pickup, card/bank
	// transfer only for delivery) lives entirely in assets/checkout.js —
	// purely visual. A server-side gateway filter was tried here first, but
	// restricting the available gateways down to "cod" while pickup is just
	// the page's default (before any address is typed) makes WooCommerce
	// store "cod" as chosen_payment_method immediately — and Colete Online's
	// shipping quote adds a mandatory bank-repayment requirement whenever that
	// session value is "cod", which fails on any account without a
	// registered repayment bank holder name and wipes out every courier rate
	// for the rest of the session. Keeping all gateways available server-side
	// avoids ever poisoning the quote; the JS only hides/relabels the choice.
}
