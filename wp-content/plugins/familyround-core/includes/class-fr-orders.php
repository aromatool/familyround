<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ce se întâmplă automat când o comandă e plătită: alocarea Pușculiței,
 * validarea recomandării și agregarea punctului pe Hartă.
 *
 * Harta se actualizează azi la confirmarea plății, nu la livrare — pentru că
 * nu avem încă integrare de curier/AWB care să semnaleze "livrat". Se mută
 * pe hook-ul de livrare odată ce integrarea de curier există (vezi riscurile
 * din documentul de arhitectură).
 */
class FR_Orders {

	const SALE_AMOUNT  = 5.00;
	const BONUS_AMOUNT = 5.00;

	public static function init() {
		// payment_complete acoperă gateway-urile reale (NETOPIA); order_status_processing
		// acoperă și ramburs, unde nu există un eveniment separat de "plată confirmată".
		// Alocările sunt idempotente (INSERT IGNORE pe chei unice), deci dublarea nu e un risc.
		add_action( 'woocommerce_payment_complete', array( __CLASS__, 'on_payment_complete' ) );
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'on_payment_complete' ) );
		add_action( 'woocommerce_order_status_cancelled', array( __CLASS__, 'on_cancelled_or_refunded' ) );
		add_action( 'woocommerce_order_status_refunded', array( __CLASS__, 'on_cancelled_or_refunded' ) );
	}

	public static function on_payment_complete( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		self::allocate_piggybank_sale( $order );
		self::process_referral( $order );
		self::aggregate_map_point( $order );
	}

	private static function allocate_piggybank_sale( $order ) {
		$mission_id = FR_DB::get_active_mission_id();
		if ( ! $mission_id ) {
			return;
		}

		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"INSERT IGNORE INTO {$wpdb->prefix}fr_piggybank_ledger
				 (order_id, entry_type, amount, mission_id, status, created_at)
				 VALUES (%d, 'vanzare', %f, %d, 'allocated', %s)",
				$order->get_id(),
				self::SALE_AMOUNT,
				$mission_id,
				current_time( 'mysql' )
			)
		);
	}

	private static function process_referral( $order ) {
		$code = $order->get_meta( '_fr_referral_code' );
		if ( ! $code ) {
			return;
		}

		global $wpdb;
		$referrer = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}fr_referrers WHERE referral_code = %s",
				$code
			)
		);
		if ( ! $referrer ) {
			return;
		}

		$customer_email = $order->get_billing_email();
		$reason         = null;

		if ( $customer_email && strtolower( $customer_email ) === strtolower( $referrer->email ) ) {
			$reason = 'auto-recomandare';
		} else {
			// wc_get_orders() funcționează indiferent de modul de stocare (HPOS sau wp_posts clasic).
			$prior_orders = wc_get_orders(
				array(
					'billing_email' => $customer_email,
					'status'        => array( 'processing', 'completed' ),
					'exclude'       => array( $order->get_id() ),
					'limit'         => 1,
					'return'        => 'ids',
				)
			);
			if ( ! empty( $prior_orders ) ) {
				$reason = 'client existent';
			}
		}

		$status = $reason ? 'invalid' : 'valid';
		$bonus  = $reason ? 0 : self::BONUS_AMOUNT;

		$wpdb->query(
			$wpdb->prepare(
				"INSERT IGNORE INTO {$wpdb->prefix}fr_referral_orders
				 (order_id, referrer_id, status, rejection_reason, bonus_amount, created_at)
				 VALUES (%d, %d, %s, %s, %f, %s)",
				$order->get_id(),
				$referrer->id,
				$status,
				$reason,
				$bonus,
				current_time( 'mysql' )
			)
		);

		if ( 'valid' === $status ) {
			$mission_id = FR_DB::get_active_mission_id();
			if ( $mission_id ) {
				$wpdb->query(
					$wpdb->prepare(
						"INSERT IGNORE INTO {$wpdb->prefix}fr_piggybank_ledger
						 (order_id, entry_type, amount, mission_id, status, created_at)
						 VALUES (%d, 'bonus_recomandare', %f, %d, 'allocated', %s)",
						$order->get_id(),
						$bonus,
						$mission_id,
						current_time( 'mysql' )
					)
				);
			}
		}
	}

	private static function aggregate_map_point( $order ) {
		$locality = $order->get_shipping_city() ?: $order->get_billing_city();
		$county   = $order->get_shipping_state() ?: $order->get_billing_state();

		if ( ! $locality || ! $county ) {
			return;
		}

		global $wpdb;
		$table    = $wpdb->prefix . 'fr_map_points';
		$existing = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE locality = %s AND county = %s", $locality, $county )
		);
		$now = current_time( 'mysql' );

		if ( $existing ) {
			$wpdb->update(
				$table,
				array(
					'total_count'      => $existing->total_count + 1,
					'last_delivery_at' => $now,
				),
				array( 'id' => $existing->id )
			);
		} else {
			$wpdb->insert(
				$table,
				array(
					'locality'          => $locality,
					'county'            => $county,
					'total_count'       => 1,
					'first_delivery_at' => $now,
					'last_delivery_at'  => $now,
				)
			);
		}
	}

	/**
	 * O comandă anulată sau rambursată nu mai contează în misiunea curentă —
	 * dar dacă suma fusese deja marcată "utilizată", păstrăm ajustarea vizibilă
	 * separat, nu ștergem nimic.
	 */
	public static function on_cancelled_or_refunded( $order_id ) {
		global $wpdb;
		$table   = $wpdb->prefix . 'fr_piggybank_ledger';
		$entries = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE order_id = %d", $order_id ) );

		foreach ( $entries as $entry ) {
			if ( 'utilizat' === $entry->status ) {
				$wpdb->query(
					$wpdb->prepare(
						"INSERT IGNORE INTO {$table}
						 (order_id, entry_type, amount, mission_id, status, created_at)
						 VALUES (%d, 'ajustare', %f, %d, 'allocated', %s)",
						$order_id,
						-abs( $entry->amount ),
						$entry->mission_id,
						current_time( 'mysql' )
					)
				);
			} else {
				$wpdb->update( $table, array( 'status' => 'cancelled' ), array( 'id' => $entry->id ) );
			}
		}
	}
}
