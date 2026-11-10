<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the FamilyRound custom tables on plugin activation.
 * Everything here is additive to WooCommerce, never a replacement for it.
 */
class FR_DB {

	public static function activate() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$p               = $wpdb->prefix;

		$tables = array();

		$tables[] = "CREATE TABLE {$p}fr_referrers (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			first_name VARCHAR(100) NOT NULL,
			email VARCHAR(190) NOT NULL,
			referral_code VARCHAR(20) NOT NULL,
			token_hash CHAR(64) NOT NULL,
			updates_opt_in TINYINT(1) NOT NULL DEFAULT 0,
			updates_consent_at DATETIME DEFAULT NULL,
			digest_count INT UNSIGNED NOT NULL DEFAULT 0,
			digest_bonus DECIMAL(10,2) NOT NULL DEFAULT 0,
			digest_last_sent_at DATETIME DEFAULT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY referral_code (referral_code),
			UNIQUE KEY email_unique (email)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$p}fr_referral_clicks (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			referrer_id BIGINT UNSIGNED NOT NULL,
			cookie_hash CHAR(64) NOT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY referrer_id (referrer_id)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$p}fr_referrer_tokens (
			token_hash CHAR(64) NOT NULL,
			referrer_id BIGINT UNSIGNED NOT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (token_hash),
			KEY referrer_id (referrer_id)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$p}fr_referral_orders (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			order_id BIGINT UNSIGNED NOT NULL,
			referrer_id BIGINT UNSIGNED NOT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			rejection_reason VARCHAR(190) DEFAULT NULL,
			bonus_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY order_id (order_id),
			KEY referrer_id (referrer_id)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$p}fr_piggybank_ledger (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			order_id BIGINT UNSIGNED DEFAULT NULL,
			entry_type VARCHAR(20) NOT NULL,
			amount DECIMAL(10,2) NOT NULL,
			mission_id BIGINT UNSIGNED DEFAULT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'allocated',
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY order_entry (order_id, entry_type),
			KEY mission_id (mission_id)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$p}fr_missions (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			title VARCHAR(190) NOT NULL,
			description TEXT,
			goal_amount DECIMAL(10,2) NOT NULL,
			start_date DATE DEFAULT NULL,
			end_date DATE DEFAULT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			image_url VARCHAR(255) DEFAULT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$p}fr_map_points (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			locality VARCHAR(120) NOT NULL,
			county VARCHAR(80) NOT NULL,
			lat DECIMAL(9,6) DEFAULT NULL,
			lng DECIMAL(9,6) DEFAULT NULL,
			total_count INT UNSIGNED NOT NULL DEFAULT 0,
			first_delivery_at DATETIME DEFAULT NULL,
			last_delivery_at DATETIME DEFAULT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY locality_county (locality, county)
		) $charset_collate;";

		// One confirmed order is one map record; totals are derived from these rows.
		$tables[] = "CREATE TABLE {$p}fr_prelaunch_leads (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			email VARCHAR(190) NOT NULL,
			product_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY email_unique (email)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$p}fr_map_orders (
			order_id BIGINT UNSIGNED NOT NULL,
			locality VARCHAR(120) NOT NULL,
			county VARCHAR(80) NOT NULL,
			game_units INT UNSIGNED NOT NULL DEFAULT 0,
			confirmed_at DATETIME NOT NULL,
			PRIMARY KEY (order_id),
			KEY locality_county (locality, county)
		) $charset_collate;";

		foreach ( $tables as $sql ) {
			dbDelta( $sql );
		}

		update_option( 'fr_core_db_version', FR_CORE_VERSION );
	}

	public static function maybe_upgrade() {
		if ( ! did_action( 'wp_loaded' ) || ! did_action( 'woocommerce_init' ) ) {
			return;
		}
		if ( get_option( 'fr_core_db_version' ) !== FR_CORE_VERSION ) {
			self::activate();
		}
		if ( get_option( 'fr_referrer_tokens_backfilled' ) !== 'yes' ) {
			global $wpdb;
			$wpdb->query(
				"INSERT IGNORE INTO {$wpdb->prefix}fr_referrer_tokens (token_hash, referrer_id, created_at)
				 SELECT token_hash, id, NOW() FROM {$wpdb->prefix}fr_referrers"
			);
			update_option( 'fr_referrer_tokens_backfilled', 'yes' );
		}
		if ( get_option( 'fr_map_backfilled_version' ) === FR_CORE_VERSION ) {
			return;
		}
		// Existing map totals were not idempotent. Rebuild from eligible Woo
		// orders instead of copying potentially duplicated aggregate numbers.
		if ( ! function_exists( 'wc_get_orders' ) ) {
			return;
		}
		global $wpdb;
		$mapped_ids = $wpdb->get_col( "SELECT order_id FROM {$wpdb->prefix}fr_map_orders" );
		$skipped_ids = array();
		foreach ( $mapped_ids as $mapped_id ) {
			try {
				$mapped_order = wc_get_order( (int) $mapped_id );
			} catch ( Throwable $error ) {
				$mapped_order = false;
				$skipped_ids[] = (int) $mapped_id;
			}
			if ( ! $mapped_order || ! $mapped_order->has_status( FR_Orders::CONFIRMED_STATUSES ) ) {
				$wpdb->delete( $wpdb->prefix . 'fr_map_orders', array( 'order_id' => (int) $mapped_id ) );
			}
		}
		$page = 1;
		do {
			try {
				$order_ids = wc_get_orders( array(
					'status' => FR_Orders::CONFIRMED_STATUSES,
					'limit'  => 100,
					'paged'  => $page++,
					'return' => 'ids',
				) );
			} catch ( Throwable $error ) {
				update_option( 'fr_map_backfill_error', $error->getMessage() );
				return;
			}
			foreach ( $order_ids as $order_id ) {
				try {
					$order = wc_get_order( (int) $order_id );
					if ( $order ) {
						FR_Orders::record_map_order( $order );
					}
				} catch ( Throwable $error ) {
					$skipped_ids[] = (int) $order_id;
				}
			}
		} while ( count( $order_ids ) === 100 );
		update_option( 'fr_map_backfill_skipped_ids', array_values( array_unique( $skipped_ids ) ) );
		delete_option( 'fr_map_backfill_error' );
		update_option( 'fr_map_backfilled_version', FR_CORE_VERSION );
	}

	/**
	 * Misiunea Pușculiței activă acum — sursă unică, folosită de REST și de hook-urile de comandă.
	 */
	public static function get_active_mission_id() {
		global $wpdb;

		return $wpdb->get_var(
			"SELECT id FROM {$wpdb->prefix}fr_missions WHERE status = 'active' ORDER BY id DESC LIMIT 1"
		);
	}
}
