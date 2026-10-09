<?php
/**
 * Plugin Name: FamilyRound Core
 * Description: Pușculița, Programul de recomandări și Harta FamilyRound — logică proprie, independentă de temă.
 * Version: 0.1.0
 * Author: FamilyRound
 * Text Domain: familyround-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FR_CORE_VERSION', '0.1.0' );
define( 'FR_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'FR_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once FR_CORE_PATH . 'includes/class-fr-db.php';
require_once FR_CORE_PATH . 'includes/class-fr-rest.php';
require_once FR_CORE_PATH . 'includes/class-fr-referrals.php';
require_once FR_CORE_PATH . 'includes/class-fr-checkout.php';
require_once FR_CORE_PATH . 'includes/class-fr-orders.php';
require_once FR_CORE_PATH . 'includes/class-fr-admin.php';
require_once FR_CORE_PATH . 'includes/class-fr-email.php';

register_activation_hook( __FILE__, array( 'FR_DB', 'activate' ) );

add_action( 'plugins_loaded', function () {
	FR_Referrals::init();

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p>FamilyRound Core are nevoie de WooCommerce activ.</p></div>';
		} );
		return;
	}

	FR_REST::init();
	FR_Checkout::init();
	FR_Orders::init();
	FR_Admin::init();
	FR_Email::init();
} );
