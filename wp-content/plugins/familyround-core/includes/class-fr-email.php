<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adaugă, în emailul de confirmare a comenzii, contribuția la Pușculiță și
 * linkul propriu de recomandare — fiecare cumpărător devine, automat, un
 * recomandator eligibil (conform documentului de arhitectură, secțiunea 8).
 */
class FR_Email {

	// "On-hold" is included because bank transfer (our only active payment method
	// for now) puts every order there first — without it, that email skipped our
	// branding and the piggybank/referral block entirely.
	const CUSTOMER_EMAIL_IDS = array( 'customer_processing_order', 'customer_completed_order', 'customer_on_hold_order' );

	public static function init() {
		foreach ( self::CUSTOMER_EMAIL_IDS as $email_id ) {
			// The default WC heading ("Thank you for your order") would duplicate
			// the greeting add_order_introduction() already renders below it.
			add_filter( 'woocommerce_email_heading_' . $email_id, '__return_empty_string' );
		}
		// WooCommerce's own Romanian translation gives on-hold and processing
		// the exact same subject line ("a fost primită!") even though they're
		// genuinely different moments — in an inbox list that reads as a
		// pointless duplicate. Make the subjects match the (already distinct)
		// body copy in add_order_introduction()/order_introduction_copy().
		add_filter( 'woocommerce_email_subject_customer_on_hold_order', function () {
			return 'Comanda ta FamilyRound a fost primită!';
		} );
		add_filter( 'woocommerce_email_subject_customer_processing_order', function () {
			return 'Comanda ta FamilyRound a fost confirmată!';
		} );
		add_filter( 'woocommerce_email_styles', array( __CLASS__, 'add_order_table_styles' ) );
		add_action( 'woocommerce_email_before_order_table', array( __CLASS__, 'add_order_introduction' ), 10, 4 );
		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'add_piggybank_and_referral_block' ), 10, 4 );
		// Pickup has no "delivery address" — WC's own address block (labeled
		// "Adresa de livrare" by the theme's translation) only makes sense for a
		// courier shipment, so skip it entirely for pickup orders. Wraps the
		// exact hook WC_Emails::email_addresses() runs on (priority 20).
		add_action( 'woocommerce_email_customer_details', array( __CLASS__, 'hide_address_for_pickup_start' ), 19 );
		add_action( 'woocommerce_email_customer_details', array( __CLASS__, 'hide_address_for_pickup_end' ), 21 );
		add_action( 'woocommerce_thankyou', array( __CLASS__, 'add_thankyou_referral_block' ), 15 );

		// Colete Online fires this the moment an AWB is actually created — whether
		// the merchant clicked "Generate AWB" or it happened automatically on
		// on-hold → processing. It's the one real signal that the order is ready
		// to leave, so this is where "comanda ta e pe drum" belongs, not on a
		// guessed delay after the order email.
		add_action( 'coleteonline_awb_created', array( __CLASS__, 'send_shipping_notification' ), 10, 2 );
		add_action( 'fr_send_experience_invite', array( __CLASS__, 'send_experience_invite' ) );

		// Pickup orders never get an AWB, so the experience-invite scheduling
		// above never runs for them. They have the game in hand the moment the
		// order is marked completed (picked up), so that's this path's anchor.
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'schedule_pickup_experience_invite' ) );

		// Auto-invoice only on card payment. SmartBill's own "auto-issue on
		// status" setting can't tell card from cash-on-pickup apart — both reach
		// "processing" automatically — so if it were configured that way it
		// would invoice cash orders too, which must stay manual (merchant
		// confirms the sale happened, then issues the invoice themselves from
		// the SmartBill box on the order screen). So: leave SmartBill's own
		// auto-issue setting OFF, and trigger it ourselves, explicitly, only
		// when the payment method isn't "cod". No-ops entirely until SmartBill
		// is actually installed and configured (real CUI/API credentials).
		add_action( 'woocommerce_order_payment_status_changed', array( __CLASS__, 'auto_issue_invoice_for_card_payment' ) );
	}

	/**
	 * The order table rides on WooCommerce's own generated styles (derived from
	 * the Settings → Emails colors) — this just adds the missing breathing room
	 * and a clear header/total row so it reads as designed, not dumped in.
	 */
	public static function add_order_table_styles( $css ) {
		return $css . '
			.email-order-details thead th { background-color: #ece4cf !important; color: #102f23 !important; text-transform: uppercase; font-size: 11px; letter-spacing: .04em; padding: 12px !important; }
			.email-order-details tbody td, .email-order-details tbody th { padding: 12px !important; border-bottom: 1px solid #e3dcc8 !important; }
			.email-order-details tfoot th, .email-order-details tfoot td { padding: 8px 12px !important; }
			.email-order-details tfoot tr:last-child th, .email-order-details tfoot tr:last-child td { font-size: 17px !important; font-weight: 700 !important; color: #102f23 !important; border-top: 2px solid #102f23 !important; padding-top: 14px !important; }
			table.shop_table thead th { background-color: #ece4cf !important; color: #102f23 !important; text-transform: uppercase; font-size: 11px; letter-spacing: .04em; padding: 12px !important; border: none !important; }
			table.shop_table td { padding: 12px !important; border-bottom: 1px solid #e3dcc8 !important; }
			table.shop_table tfoot tr:last-child th, table.shop_table tfoot tr:last-child td { font-size: 17px !important; font-weight: 700 !important; color: #102f23 !important; border-top: 2px solid #102f23 !important; padding-top: 14px !important; }
		';
	}

	public static function auto_issue_invoice_for_card_payment( $order_id ) {
		if ( ! function_exists( 'smartbill_create_document' ) ) {
			return; // SmartBill not installed/active.
		}
		$login = get_option( 'smartbill_plugin_options' );
		if ( empty( $login['username'] ) || empty( $login['password'] ) ) {
			return; // No real account connected yet — stay quiet, don't log a failed attempt on every order.
		}
		$order = wc_get_order( $order_id );
		if ( ! $order || 'cod' === $order->get_payment_method() ) {
			return; // Cash at pickup stays manual — merchant's own call, own timing.
		}
		if ( $order->get_meta( '_fr_invoice_auto_issued' ) ) {
			return; // Never re-issue automatically for the same order.
		}
		// Same function SmartBill's own "Emite factura" button calls — we're
		// just choosing to call it ourselves, right after payment, instead of
		// waiting for a manual click.
		smartbill_create_document( 1, $order_id );
		$order->update_meta_data( '_fr_invoice_auto_issued', time() );
		$order->save_meta_data();
	}

	// Days after the AWB is created before we invite the family to share how the
	// first challenge went. Approximates real delivery time (no courier
	// delivered-webhook exists yet) — adjust if typical delivery time changes.
	const EXPERIENCE_INVITE_DELAY_DAYS = 7;

	/**
	 * "Comanda ta e pe drum" — sent once per order, the moment Colete Online
	 * creates a real AWB (manual click or automatic on-hold→processing).
	 * Includes the SmartBill invoice link when one already exists: SmartBill's
	 * own status-based auto-issue hook runs earlier in the same status
	 * transition than this one, so as long as it's set to issue on the same
	 * status Colete Online auto-creates the AWB on, the invoice is already
	 * there by the time this fires. Until SmartBill is actually configured,
	 * this section just stays empty — no invoice is claimed that doesn't exist.
	 */
	public static function schedule_pickup_experience_invite( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || ! self::is_local_pickup( $order ) ) {
			return;
		}
		if ( $order->get_meta( '_fr_experience_invite_scheduled' ) ) {
			return;
		}
		$order->update_meta_data( '_fr_experience_invite_scheduled', time() );
		$order->save_meta_data();
		if ( function_exists( 'as_schedule_single_action' ) ) {
			as_schedule_single_action(
				time() + self::EXPERIENCE_INVITE_DELAY_DAYS * DAY_IN_SECONDS,
				'fr_send_experience_invite',
				array( 'order_id' => $order_id ),
				'familyround'
			);
		}
	}

	public static function send_shipping_notification( $order, $result ) {
		if ( ! is_a( $order, 'WC_Order' ) || empty( $result->awb ) ) {
			return;
		}
		// AWB creation can repeat for the same order (a re-generate, or a split
		// shipment) — the customer should only ever get this once.
		if ( $order->get_meta( '_fr_shipping_email_sent' ) ) {
			return;
		}
		$email = $order->get_billing_email();
		if ( ! $email ) {
			return;
		}

		$first_name = $order->get_billing_first_name() ?: 'prieten FamilyRound';
		$tracking_url = ! empty( $result->uniqueId ) && class_exists( '\ColeteOnline\Api\Url' )
			? \ColeteOnline\Api\Url::getWebsite() . '/track/status/' . $result->uniqueId
			: '';
		$courier_name = '';
		foreach ( $order->get_shipping_methods() as $shipping_item ) {
			$name = $shipping_item->get_meta( 'service_name' );
			if ( $name ) {
				$courier_name = $name;
				break;
			}
		}

		// SmartBill's own status-change hook runs before Colete Online's AWB hook
		// in WooCommerce's transition sequence, so by the time this fires the
		// invoice (if SmartBill is configured to issue on the same status) is
		// already there.
		$invoice = self::invoice_link_for_order( $order );

		$html = '<div style="font-family: Arial, Helvetica, sans-serif; max-width: 560px; margin: 0 auto;">'
			. '<div style="padding: 34px 32px; background-color: #102f23; text-align: center;">'
			. '<img src="' . esc_url( get_theme_file_uri( 'assets/images/logo-icon.png' ) ) . '" width="44" height="42" alt="" style="display: block; margin: 0 auto 14px;">'
			. '<p style="margin: 0; color: #d4b77d; font-size: 12px; font-weight: 700; letter-spacing: 1.5px;">FAMILYROUND</p>'
			. '<p style="margin: 10px 0 0; color: #fffaf0; font-family: Georgia, \'Times New Roman\', serif; font-size: 27px; line-height: 1.25;">Comanda ta e pe drum, ' . esc_html( $first_name ) . '!</p>'
			. '</div>'
			. '<div style="padding: 30px 32px; background-color: #f5efe3;">'
			. '<div style="margin: 0; padding: 28px; background-color: #fdfbf5; border: 1px solid #ecdfc0; border-radius: 14px; color: #102f23;">'
			. '<table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 0 18px;"><tr>'
			. '<td width="56" valign="middle"><table role="presentation" cellpadding="0" cellspacing="0" style="background-color: #e9efe2; border-radius: 999px;"><tr><td width="48" height="48" align="center" valign="middle" style="font-size: 24px; line-height: 48px;">📦</td></tr></table></td>'
			. '<td valign="middle" style="padding-left: 14px; font-size: 15px; line-height: 1.6;">Comanda ta <strong>#' . esc_html( $order->get_order_number() ) . '</strong> a fost predată curierului' . ( $courier_name ? ' <strong>' . esc_html( $courier_name ) . '</strong>' : '' ) . '. În curând, provocările voastre vor fi gata de deschis.</td>'
			. '</tr></table>'
			. '<p style="margin: 0 0 7px; font-size: 12px; font-weight: 700; letter-spacing: .04em; color: #102f23;">' . ( $courier_name ? esc_html( strtoupper( $courier_name ) ) . ' — ' : '' ) . 'NUMĂR AWB</p>'
			. '<p style="margin: 0 0 20px; padding: 14px 16px; background-color: #fffaf0; border: 1px solid #ecdfc0; border-radius: 8px; font-size: 21px; font-weight: 700; letter-spacing: .07em; color: #102f23;">' . esc_html( $result->awb ) . '</p>'
			. ( $tracking_url
				? '<p style="margin: 0 0 18px; text-align: center;"><a href="' . esc_url( $tracking_url ) . '" style="display: inline-block; padding: 13px 26px; background-color: #102f23; color: #fffaf0; text-decoration: none; font-weight: 700; border-radius: 8px;">Urmărește coletul →</a></p>'
				: '' )
			. ( $invoice
				? '<p style="margin: 0; padding-top: 16px; border-top: 1px solid #ecdfc0; font-size: 14px; text-align: center;"><a href="' . esc_url( $invoice['url'] ) . '" style="color: #102f23; font-weight: 700;">📄 Descarcă factura' . ( $invoice['label'] ? ' ' . esc_html( $invoice['label'] ) : '' ) . ' →</a></p>'
				: '' )
			. '</div></div></div>';

		$sent = wp_mail(
			$email,
			'Comanda ta FamilyRound e pe drum!',
			$html,
			array( 'Content-Type: text/html; charset=UTF-8' )
		);
		if ( $sent ) {
			$order->update_meta_data( '_fr_shipping_email_sent', time() );
			$order->save_meta_data();
		} else {
			error_log( 'FR_Email::send_shipping_notification — failed for order ' . $order->get_id() );
		}

		if ( function_exists( 'as_schedule_single_action' ) ) {
			as_schedule_single_action(
				time() + self::EXPERIENCE_INVITE_DELAY_DAYS * DAY_IN_SECONDS,
				'fr_send_experience_invite',
				array( 'order_id' => $order->get_id() ),
				'familyround'
			);
		}
	}

	/**
	 * A few days after shipping, thank the family, ask them to tag us if they
	 * post about the game, and remind them their link doubles the cause's
	 * contribution. Skips silently if the order was cancelled/refunded.
	 */
	public static function send_experience_invite( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || $order->has_status( array( 'cancelled', 'refunded', 'failed' ) ) ) {
			return;
		}
		$email = $order->get_billing_email();
		if ( ! $email ) {
			return;
		}
		$first_name = $order->get_billing_first_name() ?: 'prieten FamilyRound';
		$referrer = class_exists( 'FR_Referrals' ) ? FR_Referrals::upsert_referrer_with_fresh_token( $first_name, $email ) : null;
		$stats = $referrer && ! is_wp_error( $referrer ) ? FR_Referrals::public_stats_link( $referrer->referral_code ) : '';

		global $wpdb;
		$contributed = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}fr_piggybank_ledger WHERE order_id = %d AND status != 'cancelled'",
				$order_id
			)
		);
		$amount = $contributed > 0 ? number_format_i18n( $contributed, 0 ) : '5';

		$html = '<div style="font-family: Georgia, \'Times New Roman\', serif; max-width: 560px; margin: 0 auto;">'
			. '<div style="padding: 30px 32px; background-color: #102f23; text-align: center;">'
			. '<p style="margin: 0 0 10px; color: #d4b77d; font-family: Arial, Helvetica, sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 1.5px;">FAMILYROUND</p>'
			. '<p style="margin: 0; color: #fffaf0; font-size: 24px; line-height: 1.35;">Ne arătați și nouă un strop din povestea voastră?</p>'
			. '</div>'
			. '<img src="' . esc_url( get_theme_file_uri( 'assets/images/product/complete_product.webp' ) ) . '" width="560" alt="Cutia FamilyRound, cu cartonașele și posterul de amintiri" style="display: block; width: 100%; max-width: 560px;">'
			. '<div style="padding: 36px 34px; background-color: #fffaf0; color: #102f23; font-size: 16px; line-height: 1.75;">'
			. '<p style="margin: 0 0 20px;"><strong>Bună, ' . esc_html( $first_name ) . '!</strong></p>'
			. '<p style="margin: 0 0 20px;">Ați apucat să descoperiți FamilyRound? Poate ați încercat deja o provocare sau poate jocul încă își așteaptă momentul potrivit.</p>'
			. '<p style="margin: 0 0 24px;">Oricum ar fi, vrem să vă mulțumim încă o dată că ați ales să faceți parte din povestea noastră.</p>'
			. '<div style="margin: 0 0 24px; padding: 24px; background-color: #f5efe3; border-radius: 14px;">'
			. '<table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 0 14px;"><tr>'
			. '<td width="64" valign="middle"><table role="presentation" cellpadding="0" cellspacing="0" style="background-color: #fffaf0; border-radius: 999px;"><tr><td width="56" height="56" align="center" valign="middle" style="font-size: 28px; line-height: 56px;">📷</td></tr></table></td>'
			. '<td valign="middle" style="padding-left: 16px; font-family: Arial, Helvetica, sans-serif; font-size: 17px; font-weight: 700;">Ne-ar bucura tare mult sprijinul vostru.</td>'
			. '</tr></table>'
			. '<p style="margin: 0 0 14px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6;">Dacă vă place ceea ce am creat, ne-ar ajuta enorm să împărtășiți și altor părinți experiența voastră. Poate fi o fotografie cu jocul, un story sau un moment dintr-o provocare.</p>'
			. '<p style="margin: 0; padding: 12px 14px; background-color: #f3cf7e; border-radius: 8px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; font-weight: 700; text-align: center;">Etichetați-ne pe Facebook sau Instagram: @familyround.ro</p>'
			. '</div>'
			. '<p style="margin: 0 0 24px;">Nu trebuie să fie nimic perfect. Ne plac tocmai momentele simple și autentice.</p>'
			. ( $stats
				? '<div style="margin: 0 0 28px; padding: 24px; background-color: #f5efe3; border-radius: 14px;">'
				. '<table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 0 14px;"><tr>'
				. '<td width="64" valign="middle"><table role="presentation" cellpadding="0" cellspacing="0" style="background-color: #fffaf0; border-radius: 999px;"><tr><td width="56" height="56" align="center" valign="middle" style="font-size: 28px; line-height: 56px;">🫙</td></tr></table></td>'
				. '<td valign="middle" style="padding-left: 16px; font-family: Arial, Helvetica, sans-serif; font-size: 17px; font-weight: 700;">Și mai e un fel prin care puteți face bine.</td>'
				. '</tr></table>'
				. '<p style="margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6;">Din comanda voastră, <strong style="background-color: #f3cf7e; padding: 1px 6px; border-radius: 5px;">' . esc_html( $amount ) . ' lei</strong> au mers deja în Pușculița FamilyRound. Dacă o altă familie comandă prin linkul vostru personal, contribuția pentru acel joc devine <strong style="background-color: #f3cf7e; padding: 1px 6px; border-radius: 5px;">10 lei</strong>, fără cost suplimentar pentru nimeni.</p>'
				. '</div>'
				. '<p style="margin: 0 0 28px; text-align: center;"><a href="' . esc_url( $stats ) . '" style="display: inline-block; padding: 14px 28px; background-color: #102f23; color: #fffaf0; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-weight: 700; border-radius: 10px;">➚ Dă timpul mai departe →</a></p>'
				: '' )
			. '<p style="margin: 0 0 24px; font-style: italic; text-align: center; font-size: 15px; color: #3a5142;">Vă mulțumim că ne ajutați să creștem, pas cu pas, și să aducem mai multe familii împreună.</p>'
			. '<p style="margin: 0 0 4px;">Cu drag,</p>'
			. '<p style="margin: 0 0 2px; font-weight: 700;">Alexandru &amp; Alexandra</p>'
			. '<p style="margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #4c6253;">FamilyRound</p>'
			. '</div></div>';

		if ( ! wp_mail( $email, 'Ne arătați și nouă un strop din povestea voastră?', $html, array( 'Content-Type: text/html; charset=UTF-8' ) ) ) {
			error_log( 'FR_Email::send_experience_invite — failed for order ' . $order_id );
		}
	}

	private static function share_text( $link ) {
		$message = 'Vreau să-ți arăt ceva ce ne-a plăcut mult în familie: FamilyRound. Au niște jocuri care chiar te scot din rutină și vă apropie unii de alții. Și fac și ceva frumos — din fiecare comandă pun bani deoparte pentru o cauză. Dacă încerci prin linkul meu, la prima ta comandă pun chiar mai mult deoparte, fără să te coste nimic în plus.';
		return $link ? $message . ' ' . $link : $message;
	}

	private static function is_customer_confirmation( $order, $sent_to_admin, $email ) {
		return ! $sent_to_admin && $order && $email && in_array( $email->id, self::CUSTOMER_EMAIL_IDS, true );
	}

	/**
	 * SmartBill issues invoices instantly on card payment but only manually on
	 * cash/bank transfer (merchant confirms first) — so by the time an email
	 * sends, the invoice may or may not exist yet. Both the order-confirmation
	 * and the shipping email check this and only ever show a link when one is
	 * actually there; neither ever claims an invoice that doesn't exist.
	 */
	private static function invoice_link_for_order( $order ) {
		$url = $order->get_meta( 'smartbill_private_link' ) ?: get_post_meta( $order->get_id(), 'smartbill_private_link', true );
		if ( ! $url ) {
			return null;
		}
		$log   = $order->get_meta( 'smartbill_invoice_log' ) ?: get_post_meta( $order->get_id(), 'smartbill_invoice_log', true );
		$label = ( is_array( $log ) && ! empty( $log['smartbill_series'] ) && ! empty( $log['smartbill_invoice_id'] ) )
			? $log['smartbill_series'] . ' ' . $log['smartbill_invoice_id']
			: '';
		return array( 'url' => $url, 'label' => $label );
	}

	public static function hide_address_for_pickup_start( $order ) {
		if ( is_a( $order, 'WC_Order' ) && self::is_local_pickup( $order ) ) {
			ob_start();
		}
	}

	public static function hide_address_for_pickup_end( $order ) {
		if ( is_a( $order, 'WC_Order' ) && self::is_local_pickup( $order ) ) {
			ob_end_clean();
		}
	}

	private static function is_local_pickup( $order ) {
		foreach ( $order->get_shipping_methods() as $item ) {
			if ( 'local_pickup' === $item->get_method_id() ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * on-hold only happens for bank transfer (not a real payment option — card
	 * and cash-on-pickup both confirm instantly and skip straight to
	 * "processing"), so it keeps its own distinct copy for that wait. The
	 * instant-confirm copy itself still needs to differ: a pickup order never
	 * gets an AWB email, so it shouldn't promise one.
	 */
	private static function order_introduction_copy( $email_id, $order ) {
		$name = $order->get_billing_first_name();
		$who  = $name ? ', ' . $name : '';
		if ( 'customer_on_hold_order' === $email_id ) {
			return array(
				'heading' => 'Comanda ta a fost primită' . $who . '!',
				'body'    => 'Te contactăm în curând, prin telefon sau email, pentru plată și factură. Îți trimitem un alt email de îndată ce plasăm comanda spre livrare.',
			);
		}
		if ( self::is_local_pickup( $order ) ) {
			return array(
				'heading' => 'Comanda ta e gata de ridicat' . $who . '!',
				'body'    => 'Ai ales timp de petrecut împreună. Mai jos găsești sumarul comenzii. Te contactăm în curând ca să stabilim ridicarea.',
			);
		}
		return array(
			'heading' => 'Mulțumim' . $who . '!',
			'body'    => 'Ai ales timp de petrecut împreună. Mai jos găsești sumarul comenzii. De îndată ce pregătim coletul pentru livrare, primești un alt email cu numărul AWB.',
		);
	}

	public static function add_order_introduction( $order, $sent_to_admin, $plain_text, $email ) {
		if ( ! self::is_customer_confirmation( $order, $sent_to_admin, $email ) ) {
			return;
		}
		$copy = self::order_introduction_copy( $email->id, $order );
		$invoice = self::invoice_link_for_order( $order );
		if ( $plain_text ) {
			echo "\n" . wp_strip_all_tags( $copy['heading'] ) . "\n";
			echo wp_strip_all_tags( $copy['body'] ) . "\n\n";
			if ( $invoice ) {
				echo "Descarcă factura" . ( $invoice['label'] ? ' ' . $invoice['label'] : '' ) . ": {$invoice['url']}\n\n";
			}
			return;
		}
		?>
		<div style="margin: 22px 0 28px; padding: 27px 30px; background-color: #102f23; color: #fffaf0;">
			<p style="margin: 0 0 10px; color: #d4b77d; font-size: 12px; font-weight: 700; letter-spacing: 1.5px;">FAMILYROUND</p>
			<p style="margin: 0 0 10px; color: #fffaf0; font-family: Georgia, serif; font-size: 29px; line-height: 1.2;"><?php echo esc_html( $copy['heading'] ); ?></p>
			<p style="margin: 0; color: #f3eddf; font-size: 15px; line-height: 1.65;"><?php echo esc_html( $copy['body'] ); ?></p>
			<?php if ( $invoice ) : ?>
				<p style="margin: 15px 0 0; padding-top: 15px; border-top: 1px solid rgba(255,255,255,.2); font-size: 15px;"><a href="<?php echo esc_url( $invoice['url'] ); ?>" style="color: #fffaf0; font-weight: 700;">Descarcă factura<?php echo $invoice['label'] ? ' ' . esc_html( $invoice['label'] ) : ''; ?> →</a></p>
			<?php endif; ?>
		</div>
		<?php
	}

	public static function add_piggybank_and_referral_block( $order, $sent_to_admin, $plain_text, $email ) {
		if ( ! self::is_customer_confirmation( $order, $sent_to_admin, $email ) ) {
			return;
		}

		$billing_email = $order->get_billing_email();
		$first_name    = $order->get_billing_first_name() ?: 'prieten FamilyRound';

		if ( ! $billing_email ) {
			return;
		}

		global $wpdb;
		$contributed = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}fr_piggybank_ledger
				 WHERE order_id = %d AND status != 'cancelled'",
				$order->get_id()
			)
		);

		$referrer = FR_Referrals::upsert_referrer_with_fresh_token( $first_name, $billing_email );
		if ( is_wp_error( $referrer ) ) {
			return;
		}
		$stats = FR_Referrals::public_stats_link( $referrer->referral_code );
		$units = FR_Orders::eligible_units( $order );
		$expected = $units * FR_Orders::SALE_AMOUNT;

		$amount = $contributed > 0 ? $contributed : $expected;
		if ( $amount <= 0 ) {
			return;
		}

		if ( $plain_text ) {
			echo "\n\nPUȘCULIȚA FAMILYROUND\n";
			echo "Comanda ta a pus deja {$amount} lei deoparte.\n\n";
			echo "Din comanda ta de acum, {$amount} lei merg deja în Pușculița FamilyRound — un mic gest care aduce timp împreună, fără cost în plus pentru tine.\n\n";
			echo "Dacă o altă familie comandă prin linkul tău, în Pușculiță intră 10 lei.\n";
			echo "Tu doar dai linkul mai departe. Noi dublăm binele făcut.\n\n";
			echo "Linkul tău personal: {$referrer->referral_code} — {$stats}\n\n";
			echo "Fără cost în plus pentru tine sau pentru familia care comandă.\n\n";
			echo "1. Trimiți linkul · 2. O familie comandă · 3. În Pușculiță intră 10 lei\n\n";
			echo "Îți mulțumim că ajuți timpul frumos să ajungă mai departe.\n";
			return;
		}
		?>
		<div style="margin: 30px 0; padding: 28px; background-color: #fdfbf5; border: 1px solid #ecdfc0; border-radius: 14px; color: #102f23;">
			<p style="margin: 0 0 18px;"><span style="display: inline-block; padding: 6px 14px 6px 10px; background-color: #f5efe3; border-radius: 999px; font-size: 12px; font-weight: 700; letter-spacing: .03em; color: #8a6d3b;"><span style="font-size: 18px; vertical-align: middle;">🫙</span> <span style="vertical-align: middle;">Pușculița FamilyRound</span></span></p>
			<h3 style="margin: 0 0 14px; font-family: Georgia, 'Times New Roman', serif; font-weight: 700; font-size: 24px; line-height: 1.3;">Comanda ta a pus deja <span style="background-color: #f3cf7e; padding: 1px 8px; border-radius: 6px;"><?php echo esc_html( number_format_i18n( $amount, 0 ) ); ?> lei</span> deoparte</h3>
			<p style="margin: 0 0 22px; font-size: 15px; line-height: 1.6;">Din comanda ta de acum, <?php echo esc_html( number_format_i18n( $amount, 0 ) ); ?> lei merg deja în <em>Pușculița FamilyRound</em> — un mic gest care aduce timp împreună, fără cost în plus pentru tine.</p>
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 0 0 20px; background-color: #e9efe2; border-radius: 10px;">
				<tr>
					<td width="64" align="center" valign="middle" style="padding: 16px 0 16px 18px;">
						<table role="presentation" cellpadding="0" cellspacing="0" style="background-color: #fffaf0; border-radius: 999px;"><tr><td width="52" height="52" align="center" valign="middle" style="font-size: 28px; line-height: 52px;">🫙</td></tr></table>
					</td>
					<td style="padding: 18px 20px 18px 16px; font-size: 16px; line-height: 1.5;">Dacă o altă familie comandă prin linkul tău, în Pușculiță intră <strong style="background-color: #f3cf7e; padding: 1px 8px; border-radius: 6px; white-space: nowrap;">10 lei</strong>.</td>
				</tr>
			</table>
			<p style="margin: 0 0 22px; font-style: italic; text-align: center; font-size: 15px; color: #3a5142;">Tu doar dai linkul mai departe. Noi dublăm binele făcut.</p>
			<p style="margin: 0 0 7px; font-size: 12px; font-weight: 700; letter-spacing: .04em;">LINKUL TĂU PERSONAL</p>
			<p style="margin: 0 0 18px; padding: 13px 16px; background-color: #fffaf0; border: 1px solid #ecdfc0; border-radius: 8px; overflow-wrap: anywhere; font-size: 15px; font-weight: 700;"><?php echo esc_html( $referrer->referral_code ); ?></p>
			<p style="margin: 0 0 10px; text-align: center;"><a href="<?php echo esc_url( $stats ); ?>" style="display: inline-block; padding: 13px 26px; background-color: #102f23; color: #fffaf0; text-decoration: none; font-weight: 700; border-radius: 8px;"><span style="font-size: 16px; vertical-align: middle;">➤</span> <span style="vertical-align: middle;">Dă mai departe linkul tău →</span></a></p>
			<p style="margin: 0 0 24px; text-align: center; font-size: 13px; color: #4c6253;">✓ Fără cost în plus pentru tine sau pentru familia care comandă.</p>
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 0 0 20px; border-top: 1px solid #ecdfc0; border-bottom: 1px solid #ecdfc0;">
				<tr>
					<td align="center" valign="top" style="width: 33%; padding: 20px 6px;">
						<table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin: 0 auto 10px; background-color: #f5efe3; border-radius: 999px;"><tr><td width="48" height="48" align="center" valign="middle" style="font-size: 24px; line-height: 48px;">📨</td></tr></table>
						<p style="margin: 0; font-size: 13px; line-height: 1.4;"><strong>1.</strong> Trimiți linkul</p>
					</td>
					<td align="center" valign="top" style="width: 33%; padding: 20px 6px; border-left: 1px solid #ecdfc0; border-right: 1px solid #ecdfc0;">
						<table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin: 0 auto 10px; background-color: #f5efe3; border-radius: 999px;"><tr><td width="48" height="48" align="center" valign="middle" style="font-size: 24px; line-height: 48px;">🛒</td></tr></table>
						<p style="margin: 0; font-size: 13px; line-height: 1.4;"><strong>2.</strong> O familie comandă</p>
					</td>
					<td align="center" valign="top" style="width: 33%; padding: 20px 6px;">
						<table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin: 0 auto 10px; background-color: #f3cf7e; border-radius: 999px;"><tr><td width="48" height="48" align="center" valign="middle" style="font-size: 24px; line-height: 48px;">🪙</td></tr></table>
						<p style="margin: 0; font-size: 13px; line-height: 1.4;"><strong>3.</strong> Intră <strong>10 lei</strong></p>
					</td>
				</tr>
			</table>
			<p style="margin: 0; text-align: center; font-style: italic; font-size: 14px; color: #3a5142;">Îți mulțumim că ajuți timpul frumos să ajungă mai departe. <span style="font-size: 16px; vertical-align: middle;">🤍</span></p>
		</div>
		<?php
	}

	public static function add_thankyou_referral_block( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || $order->has_status( array( 'failed', 'cancelled', 'refunded' ) ) ) {
			return;
		}
		$link = FR_Referrals::link_for_customer( $order->get_billing_first_name() ?: 'prieten FamilyRound', $order->get_billing_email() );
		if ( ! $link ) {
			return;
		}
		$message = self::share_text( $link );
		$whatsapp = fr_whatsapp_link( $message );
		global $wpdb;
		$contributed = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}fr_piggybank_ledger
				 WHERE order_id = %d AND status != 'cancelled'",
				$order_id
			)
		);
		?>
		<section class="fr-thanks-share" aria-labelledby="fr-thanks-share-title">
			<p class="fr-thanks-share-kicker">Dă timpul mai departe</p>
			<h2 id="fr-thanks-share-title">Bucuria asta poate ajunge și la altă familie.</h2>
			<?php if ( $contributed > 0 ) : ?>
				<p>Din comanda ta am alocat deja <strong><?php echo esc_html( number_format_i18n( $contributed, 0 ) ); ?> lei</strong> în Pușculița FamilyRound.</p>
			<?php else : ?>
				<p>După confirmarea comenzii, vom pune deoparte contribuția FamilyRound pentru jocurile cumpărate.</p>
			<?php endif; ?>
			<p>Acesta este linkul tău personal. La prima comandă a fiecărui cumpărător nou venit prin el, noi mai adăugăm 5 lei pentru fiecare joc eligibil în Pușculiță. Poți recomanda oricâte familii noi; nimeni nu plătește în plus.</p>
			<label for="fr-thanks-referral-link">Linkul tău de recomandare</label>
			<input id="fr-thanks-referral-link" readonly value="<?php echo esc_attr( $link ); ?>" onclick="this.select()">
			<div class="fr-thanks-share-actions">
				<button type="button" data-fr-share-action="native" data-fr-share-url="<?php echo esc_attr( $link ); ?>" data-fr-share-text="<?php echo esc_attr( self::share_text( '' ) ); ?>">Distribuie linkul ↗</button>
				<button type="button" data-fr-share-action="copy" data-fr-share-url="<?php echo esc_attr( $link ); ?>">Copiază linkul</button>
				<a href="<?php echo esc_url( $whatsapp ); ?>" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a>
			</div>
			<p class="fr-thanks-share-status" data-fr-share-status role="status" aria-live="polite"></p>
			<p class="fr-thanks-share-foot">Ți-am trimis codul și pe email. Îl poți introduce oricând în „Vezi impactul” ca să urmărești recomandările validate. Poți adăuga propriul mesaj sau o fotografie atunci când primești jocul.</p>
		</section>
		<?php
	}
}
