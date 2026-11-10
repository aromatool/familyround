<?php
/**
 * Plugin Name: FamilyRound Core
 * Description: Pușculița, Programul de recomandări și Harta FamilyRound — logică proprie, independentă de temă.
 * Version: 0.2.6
 * Author: FamilyRound
 * Text Domain: familyround-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FR_CORE_VERSION', '0.2.6' );
define( 'FR_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'FR_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once FR_CORE_PATH . 'includes/class-fr-db.php';
require_once FR_CORE_PATH . 'includes/class-fr-rest.php';
require_once FR_CORE_PATH . 'includes/class-fr-referrals.php';
require_once FR_CORE_PATH . 'includes/class-fr-referral-digest.php';
require_once FR_CORE_PATH . 'includes/class-fr-checkout.php';
require_once FR_CORE_PATH . 'includes/class-fr-orders.php';
require_once FR_CORE_PATH . 'includes/class-fr-admin.php';
require_once FR_CORE_PATH . 'includes/class-fr-email.php';
require_once FR_CORE_PATH . 'includes/class-fr-prelaunch.php';

register_activation_hook( __FILE__, array( 'FR_DB', 'activate' ) );

add_action( 'plugins_loaded', function () {
	FR_Referrals::init();
	FR_Referral_Digest::init();

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p>FamilyRound Core are nevoie de WooCommerce activ.</p></div>';
		} );
		return;
	}

	// The order backfill needs WooCommerce and its order post types fully loaded.
	add_action( 'wp_loaded', array( 'FR_DB', 'maybe_upgrade' ), 20 );
	add_action( 'admin_notices', function () {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$skipped = array_map( 'intval', (array) get_option( 'fr_map_backfill_skipped_ids', array() ) );
		if ( $skipped ) {
			echo '<div class="notice notice-warning"><p>FamilyRound: harta nu a putut citi comenzile WooCommerce cu ID-urile ' . esc_html( implode( ', ', $skipped ) ) . '. Verifică aceste înregistrări înainte de lansare.</p></div>';
		}
	} );

	FR_REST::init();
	FR_Checkout::init();
	FR_Orders::init();
	FR_Admin::init();
	FR_Email::init();
	FR_Prelaunch::init();

	// Stand-in card gateway for local testing while NETOPIA has no real
	// account connected. Disappears on its own the moment NETOPIA itself is
	// enabled with real credentials — delete class-fr-test-card-gateway.php
	// then too, nothing else references it. Also stays off while
	// FR_PAYMENT_AWAITING_CUI is true (see functions.php) — that mode takes
	// real delivery orders with no card at all, bank transfer standing in as
	// "pay after we contact you".
	add_filter( 'woocommerce_payment_gateways', function ( $gateways ) {
		$netopia_settings = get_option( 'woocommerce_netopiapayments_settings' );
		$netopia_enabled  = is_array( $netopia_settings ) && 'yes' === ( $netopia_settings['enabled'] ?? '' );
		if ( ! $netopia_enabled && ! ( function_exists( 'fr_payment_awaiting_cui' ) && fr_payment_awaiting_cui() ) ) {
			require_once FR_CORE_PATH . 'includes/class-fr-test-card-gateway.php';
			$gateways[] = 'FR_Test_Card_Gateway';
		}
		return $gateways;
	} );

	// Bank transfer stands in for card at checkout until Netopia is live —
	// once it's enabled with real credentials, drop bacs the same way the
	// test gateway above drops itself. Card takes over, ramburs (cod) stays.
	add_filter( 'woocommerce_available_payment_gateways', function ( $gateways ) {
		$netopia_settings = get_option( 'woocommerce_netopiapayments_settings' );
		$netopia_enabled  = is_array( $netopia_settings ) && 'yes' === ( $netopia_settings['enabled'] ?? '' );
		if ( $netopia_enabled ) {
			unset( $gateways['bacs'] );
		}
		return $gateways;
	} );

	// Colete Online periodically re-syncs its settings from the platform and
	// stomps this back to "yes", which blocks checkout for pickup orders (no
	// address collected at all there). Force it off on every read instead of
	// relying on the one-time wp_option_update surviving their sync.
	add_filter( 'option_coleteonline_address_validate_address_checkout', function () {
		return 'no';
	} );
} );
