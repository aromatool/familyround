<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Impactul unei comenzi confirmate. Harta folosește comenzile confirmate până
 * când curierul va putea semnala în mod sigur livrarea.
 */
class FR_Orders {
	const SALE_AMOUNT  = 5.00;
	const BONUS_AMOUNT = 5.00;
	// "Confirmed" for piggybank/map/referral purposes. Includes Colete Online's
	// own "Expediere inițiată" status: with AWB creation now manual, a merchant
	// goes straight from On-hold to that status by clicking "Creează AWB" after
	// confirming payment by phone — "processing"/"completed" alone would never
	// fire for that flow, and the sale would never get counted.
	const CONFIRMED_STATUSES = array( 'processing', 'completed', 'col-shipping-init' );

	public static function init() {
		add_action( 'woocommerce_payment_complete', array( __CLASS__, 'on_order_confirmed' ), 1 );
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'on_order_confirmed' ), 1 );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'on_order_confirmed' ), 1 );
		add_action( 'woocommerce_order_status_cancelled', array( __CLASS__, 'on_order_reversed' ) );
		add_action( 'woocommerce_order_status_refunded', array( __CLASS__, 'on_order_reversed' ) );
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'on_status_changed' ), 10, 3 );
		add_action( 'woocommerce_order_refunded', array( __CLASS__, 'on_partial_refund' ), 10, 2 );
	}

	/** Non-game products can opt out with _fr_piggybank_eligible = no. */
	public static function product_is_eligible( $product, $item = null, $order = null ) {
		if ( ! $product || $product->is_virtual() ) {
			return false;
		}
		$flag = $product->get_meta( '_fr_piggybank_eligible', true );
		if ( '' === $flag && $product->is_type( 'variation' ) ) {
			$flag = get_post_meta( $product->get_parent_id(), '_fr_piggybank_eligible', true );
		}
		return (bool) apply_filters( 'fr_game_eligible', 'no' !== $flag, $product, $item, $order );
	}

	public static function eligible_units( $order ) {
		$units = 0;
		foreach ( $order->get_items( 'line_item' ) as $item_id => $item ) {
			$snapshot = $item->get_meta( '_fr_game_eligible', true );
			$eligible = '' === $snapshot
				? self::product_is_eligible( $item->get_product(), $item, $order )
				: 'yes' === $snapshot;
			if ( ! $eligible ) {
				continue;
			}
			$refunded = method_exists( $order, 'get_qty_refunded_for_item' )
				? abs( (int) $order->get_qty_refunded_for_item( $item_id ) )
				: 0;
			$units += max( 0, (int) $item->get_quantity() - $refunded );
		}
		return $units;
	}

	/** A confirmed purchase under either contact detail makes later orders ineligible. */
	public static function has_prior_confirmed_order( $email, $phone, $exclude_order_id = 0 ) {
		$email = strtolower( trim( (string) $email ) );
		$digits = preg_replace( '/\D+/', '', (string) $phone );
		$normalized_phone = $digits;
		if ( 11 === strlen( $digits ) && 0 === strpos( $digits, '40' ) ) {
			$normalized_phone = '0' . substr( $digits, 2 );
		} elseif ( 13 === strlen( $digits ) && 0 === strpos( $digits, '0040' ) ) {
			$normalized_phone = '0' . substr( $digits, 4 );
		}
		$phone_forms = array_filter( array_unique( array(
			trim( (string) $phone ),
			$digits,
			$normalized_phone,
			10 === strlen( $normalized_phone ) && '0' === $normalized_phone[0] ? '+40' . substr( $normalized_phone, 1 ) : '',
			10 === strlen( $normalized_phone ) && '0' === $normalized_phone[0] ? '40' . substr( $normalized_phone, 1 ) : '',
			10 === strlen( $normalized_phone ) && '0' === $normalized_phone[0] ? '0040' . substr( $normalized_phone, 1 ) : '',
		) ) );
		$base = array(
			'status'  => self::CONFIRMED_STATUSES,
			'limit'   => 1,
			'return'  => 'ids',
			'exclude' => $exclude_order_id ? array( (int) $exclude_order_id ) : array(),
		);
		if ( $email && is_email( $email ) && wc_get_orders( array_merge( $base, array( 'billing_email' => $email ) ) ) ) {
			return true;
		}
		foreach ( $phone_forms as $candidate ) {
			if ( wc_get_orders( array_merge( $base, array( 'billing_phone' => $candidate ) ) ) ) {
				return true;
			}
		}
		return false;
	}

	/** Older orders receive a one-time snapshot while their product still exists. */
	public static function ensure_order_item_snapshot( $order ) {
		foreach ( $order->get_items( 'line_item' ) as $item ) {
			if ( '' !== $item->get_meta( '_fr_game_eligible', true ) ) {
				continue;
			}
			$eligible = self::product_is_eligible( $item->get_product(), $item, $order );
			$item->add_meta_data( '_fr_game_eligible', $eligible ? 'yes' : 'no', true );
			$item->save();
		}
	}

	public static function on_order_confirmed( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || ! $order->has_status( self::CONFIRMED_STATUSES ) ) {
			return;
		}
		self::ensure_order_item_snapshot( $order );
		$units = self::eligible_units( $order );
		self::sync_sale( $order, $units );
		self::record_map_order( $order, $units );
		self::sync_referral( $order, $units );
		if ( 'yes' === $order->get_meta( '_fr_ref_updates_opt_in' ) ) {
			FR_Referrals::opt_in_email( $order->get_billing_first_name(), $order->get_billing_email() );
		}
	}

	public static function on_status_changed( $order_id, $old_status, $new_status ) {
		$confirmed = self::CONFIRMED_STATUSES;
		if ( in_array( $old_status, $confirmed, true ) && ! in_array( $new_status, $confirmed, true ) ) {
			self::on_order_reversed( $order_id );
		}
		if ( ! in_array( $old_status, $confirmed, true ) && in_array( $new_status, $confirmed, true ) ) {
			self::on_order_confirmed( $order_id );
		}
	}

	private static function sync_sale( $order, $units ) {
		global $wpdb;
		$table    = $wpdb->prefix . 'fr_piggybank_ledger';
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT id, amount, status, mission_id FROM {$table} WHERE order_id = %d AND entry_type = 'vanzare'", $order->get_id() ) );
		$amount   = self::SALE_AMOUNT * $units;
		if ( $existing ) {
			if ( in_array( $existing->status, array( 'allocated', 'cancelled' ), true ) ) {
				$wpdb->update( $table, array( 'amount' => $amount, 'status' => $units ? 'allocated' : 'cancelled' ), array( 'id' => $existing->id ) );
			} else {
				self::sync_immutable_adjustment( $order->get_id(), 'vanzare', (float) $existing->amount, $amount, (int) $existing->mission_id );
			}
			if ( (float) $existing->amount !== $amount ) {
				$order->add_order_note( sprintf( 'FamilyRound: contribuția de bază ajustată de la %s la %s lei pentru %d jocuri eligibile.', $existing->amount, $amount, $units ), false );
			}
			return;
		}
		$mission_id = FR_DB::get_active_mission_id();
		if ( ! $units ) {
			return;
		}
		$wpdb->insert( $table, array(
			'order_id'   => $order->get_id(),
			'entry_type' => 'vanzare',
			'amount'     => $amount,
			'mission_id' => $mission_id ? (int) $mission_id : null,
			'status'     => 'allocated',
			'created_at' => current_time( 'mysql' ),
		) );
	}

	private static function sync_referral( $order, $units ) {
		$code = $order->get_meta( '_fr_referral_code' );
		if ( ! $code ) {
			return;
		}
		global $wpdb;
		$referrer = $wpdb->get_row( $wpdb->prepare( "SELECT id, email FROM {$wpdb->prefix}fr_referrers WHERE referral_code = %s", $code ) );
		if ( ! $referrer ) {
			return;
		}
		$table = $wpdb->prefix . 'fr_referral_orders';
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT id, referrer_id, status, bonus_amount FROM {$table} WHERE order_id = %d", $order->get_id() ) );
		$email = $order->get_billing_email();
		$reason = null;
		if ( ! $email ) {
			$reason = 'email lipsă';
		} elseif ( strcasecmp( $email, $referrer->email ) === 0 ) {
			$reason = 'auto-recomandare';
		}
		if ( ! $units ) {
			$reason = 'niciun joc eligibil';
		}
		// Honor bonuses already granted before this rule changed. New bonuses are
		// reserved for the buyer's first confirmed order, across all codes.
		$already_awarded = $existing && 'valid' === $existing->status && (float) $existing->bonus_amount > 0;
		if ( ! $reason && ! $already_awarded && self::has_prior_confirmed_order( $email, $order->get_billing_phone(), $order->get_id() ) ) {
			$reason = 'client existent';
		}
		$status = $reason ? 'invalid' : 'valid';
		$bonus  = $reason ? 0 : self::BONUS_AMOUNT * $units;
		if ( $existing ) {
			// O comandă înregistrată păstrează recomandatorul inițial.
			if ( (int) $existing->referrer_id !== (int) $referrer->id ) {
				return;
			}
			$wpdb->update( $table, array( 'status' => $status, 'rejection_reason' => $reason, 'bonus_amount' => $bonus ), array( 'id' => $existing->id ) );
		} else {
			$wpdb->insert( $table, array(
				'order_id'         => $order->get_id(),
				'referrer_id'      => $referrer->id,
				'status'           => $status,
				'rejection_reason' => $reason,
				'bonus_amount'     => $bonus,
				'created_at'       => current_time( 'mysql' ),
			) );
		}

		$ledger = $wpdb->prefix . 'fr_piggybank_ledger';
		$entry = $wpdb->get_row( $wpdb->prepare( "SELECT id, amount, status, mission_id FROM {$ledger} WHERE order_id = %d AND entry_type = 'bonus_recomandare'", $order->get_id() ) );
		if ( $entry ) {
			if ( in_array( $entry->status, array( 'allocated', 'cancelled' ), true ) ) {
				$wpdb->update( $ledger, array( 'amount' => $bonus, 'status' => $bonus ? 'allocated' : 'cancelled' ), array( 'id' => $entry->id ) );
			} else {
				self::sync_immutable_adjustment( $order->get_id(), 'bonus_recomandare', (float) $entry->amount, $bonus, (int) $entry->mission_id );
			}
			if ( (float) $entry->amount !== $bonus ) {
				$order->add_order_note( sprintf( 'FamilyRound: bonusul de recomandare ajustat de la %s la %s lei.', $entry->amount, $bonus ), false );
			}
		} else {
			$mission_id = FR_DB::get_active_mission_id();
			if ( $bonus ) {
				$wpdb->insert( $ledger, array(
					'order_id'   => $order->get_id(),
					'entry_type' => 'bonus_recomandare',
					'amount'     => $bonus,
					'mission_id' => $mission_id ? (int) $mission_id : null,
					'status'     => 'allocated',
					'created_at' => current_time( 'mysql' ),
				) );
			}
		}
	}

	/** Preserve already used/transferred entries and offset their promise separately. */
	private static function sync_immutable_adjustment( $order_id, $entry_type, $original, $target, $mission_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'fr_piggybank_ledger';
		$prefix = 'vanzare' === $entry_type ? 'ajustare_v' : 'ajustare_r';
		$adjustments = $wpdb->get_results( $wpdb->prepare(
			"SELECT entry_type, amount, status FROM {$table} WHERE order_id = %d AND entry_type LIKE %s",
			$order_id, $wpdb->esc_like( $prefix ) . '%'
		) );
		$current = $original;
		$sequence = 0;
		foreach ( $adjustments as $adjustment ) {
			if ( 'cancelled' !== $adjustment->status ) {
				$current += (float) $adjustment->amount;
			}
			$number = $adjustment->entry_type === $prefix ? 1 : (int) substr( $adjustment->entry_type, strlen( $prefix ) + 1 );
			$sequence = max( $sequence, $number );
		}
		$delta = round( $target - $current, 2 );
		if ( abs( $delta ) >= 0.01 ) {
			$wpdb->insert( $table, array(
				'order_id'   => $order_id,
				'entry_type' => $prefix . '_' . ( $sequence + 1 ),
				'amount'     => $delta,
				'mission_id' => $mission_id ?: null,
				'status'     => 'allocated',
				'created_at' => current_time( 'mysql' ),
			) );
		}
	}

	/** O înregistrare unică per comandă oprește dublarea la hook-uri repetate. */
	public static function record_map_order( $order, $units = null ) {
		self::ensure_order_item_snapshot( $order );
		$units    = null === $units ? self::eligible_units( $order ) : $units;
		$locality = $order->get_shipping_city() ?: $order->get_billing_city();
		$county   = $order->get_shipping_state() ?: $order->get_billing_state();
		if ( ( ! $locality || ! $county ) && self::is_pickup_order( $order ) ) {
			// Ridicarea nu colectează adresă — comanda apare pe hartă la sediul FamilyRound.
			$locality = 'Florești';
			$county   = 'CJ';
		}
		if ( ! $units || ! $locality || ! $county ) {
			global $wpdb;
			$wpdb->delete( $wpdb->prefix . 'fr_map_orders', array( 'order_id' => $order->get_id() ) );
			return;
		}
		global $wpdb;
		$confirmed_at = $order->get_date_paid() ?: $order->get_date_created();
		$wpdb->query( $wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}fr_map_orders (order_id, locality, county, game_units, confirmed_at)
			 VALUES (%d, %s, %s, %d, %s)
			 ON DUPLICATE KEY UPDATE locality = VALUES(locality), county = VALUES(county), game_units = VALUES(game_units)",
			$order->get_id(), $locality, $county, $units, $confirmed_at ? $confirmed_at->date( 'Y-m-d H:i:s' ) : current_time( 'mysql' )
		) );
	}

	public static function on_partial_refund( $order_id, $refund_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || $order->has_status( array( 'cancelled', 'refunded' ) ) ) {
			return;
		}
		self::on_order_confirmed( $order_id );
		if ( 0 === self::eligible_units( $order ) ) {
			global $wpdb;
			$wpdb->delete( $wpdb->prefix . 'fr_map_orders', array( 'order_id' => $order_id ) );
		}
	}

	public static function on_order_reversed( $order_id ) {
		global $wpdb;
		$order = wc_get_order( $order_id );
		if ( $order && $order->get_meta( '_fr_first_confirmed_ts' ) ) {
			$order->delete_meta_data( '_fr_first_confirmed_ts' );
			$order->save();
		}
		$ledger  = $wpdb->prefix . 'fr_piggybank_ledger';
		$entries = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$ledger} WHERE order_id = %d", $order_id ) );
		foreach ( $entries as $entry ) {
			if ( 0 === strpos( $entry->entry_type, 'ajustare_' ) ) {
				continue;
			}
			if ( ! in_array( $entry->status, array( 'allocated', 'cancelled' ), true ) ) {
				self::sync_immutable_adjustment( $order_id, $entry->entry_type, (float) $entry->amount, 0, (int) $entry->mission_id );
			} else {
				$wpdb->update( $ledger, array( 'status' => 'cancelled' ), array( 'id' => $entry->id ) );
			}
		}
		$wpdb->update( $wpdb->prefix . 'fr_referral_orders', array( 'status' => 'invalid', 'rejection_reason' => 'comandă neconfirmată', 'bonus_amount' => 0 ), array( 'order_id' => $order_id ) );
		$wpdb->delete( $wpdb->prefix . 'fr_map_orders', array( 'order_id' => $order_id ) );
	}

	private static function is_pickup_order( $order ) {
		foreach ( $order->get_shipping_methods() as $item ) {
			if ( 0 === strpos( $item->get_method_id(), 'local_pickup' ) ) {
				return true;
			}
		}
		return false;
	}
}
