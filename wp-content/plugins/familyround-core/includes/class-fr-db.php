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
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY referral_code (referral_code),
			KEY email (email)
		) $charset_collate;";

		$tables[] = "CREATE TABLE {$p}fr_referral_clicks (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			referrer_id BIGINT UNSIGNED NOT NULL,
			cookie_hash CHAR(64) NOT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
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

		foreach ( $tables as $sql ) {
			dbDelta( $sql );
		}

		update_option( 'fr_core_db_version', FR_CORE_VERSION );
	}
}
