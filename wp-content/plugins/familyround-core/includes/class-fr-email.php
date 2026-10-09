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

	const CUSTOMER_EMAIL_IDS = array( 'customer_processing_order', 'customer_completed_order' );

	public static function init() {
		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'add_piggybank_and_referral_block' ), 10, 4 );
	}

	public static function add_piggybank_and_referral_block( $order, $sent_to_admin, $plain_text, $email ) {
		if ( $sent_to_admin || ! $order ) {
			return;
		}
		if ( ! $email || ! in_array( $email->id, self::CUSTOMER_EMAIL_IDS, true ) ) {
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
				 WHERE order_id = %d AND status = 'allocated'",
				$order->get_id()
			)
		);

		$referrer = FR_Referrals::upsert_referrer_with_fresh_token( $first_name, $billing_email );
		$link     = FR_Referrals::referral_link( $referrer->referral_code );
		$dashboard = FR_Referrals::dashboard_link( $referrer->token );

		if ( $plain_text ) {
			echo "\n\n";
			if ( $contributed > 0 ) {
				echo "Mulțumim! Am alocat deja {$contributed} lei în Pușculița FamilyRound din comanda ta.\n\n";
			}
			echo "Dă timpul mai departe: distribuie linkul tău și mai aduci 5 lei în Pușculiță de fiecare dată când un prieten cumpără.\n";
			echo "Linkul tău: {$link}\n";
			echo "Vezi-ți impactul: {$dashboard}\n";
			return;
		}
		?>
		<div style="margin: 24px 0; padding: 20px; background: #f6efe4; border-radius: 8px; text-align: center;">
			<?php if ( $contributed > 0 ) : ?>
				<p style="margin: 0 0 12px; font-size: 15px;">
					🐷 Mulțumim! Am alocat deja <strong><?php echo esc_html( number_format( $contributed, 0 ) ); ?> lei</strong> în Pușculița FamilyRound din comanda ta.
				</p>
			<?php endif; ?>
			<p style="margin: 0 0 12px; font-size: 15px;">
				<strong>Dă timpul mai departe:</strong> distribuie linkul tău și mai aduci 5 lei în Pușculiță de fiecare dată când un prieten cumpără.
			</p>
			<p style="margin: 0 0 8px;">
				<a href="<?php echo esc_url( $link ); ?>" style="color: #1f3b2c; font-weight: 600;"><?php echo esc_html( $link ); ?></a>
			</p>
			<p style="margin: 0; font-size: 13px;">
				<a href="<?php echo esc_url( $dashboard ); ?>" style="color: #6b6b6b;">Vezi-ți impactul →</a>
			</p>
		</div>
		<?php
	}
}
