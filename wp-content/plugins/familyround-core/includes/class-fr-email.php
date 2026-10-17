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
		add_action( 'woocommerce_email_before_order_table', array( __CLASS__, 'add_order_introduction' ), 10, 4 );
		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'add_piggybank_and_referral_block' ), 10, 4 );
		add_action( 'woocommerce_thankyou', array( __CLASS__, 'add_thankyou_referral_block' ), 15 );
	}

	private static function share_text( $link ) {
		$message = 'Am ales o experiență FamilyRound pentru familia mea. Dacă vrei să descoperi și tu jocurile lor, intră prin linkul meu. La prima ta comandă, FamilyRound mai pune 5 lei pentru fiecare joc în Pușculiță, fără să plătești tu în plus.';
		return $link ? $message . ' ' . $link : $message;
	}

	private static function is_customer_confirmation( $order, $sent_to_admin, $email ) {
		return ! $sent_to_admin && $order && $email && in_array( $email->id, self::CUSTOMER_EMAIL_IDS, true );
	}

	public static function add_order_introduction( $order, $sent_to_admin, $plain_text, $email ) {
		if ( ! self::is_customer_confirmation( $order, $sent_to_admin, $email ) ) {
			return;
		}
		$name = $order->get_billing_first_name();
		$greeting = $name ? 'Mulțumim, ' . $name . '!' : 'Mulțumim pentru comanda ta!';
		if ( $plain_text ) {
			echo "\n" . wp_strip_all_tags( $greeting ) . "\n";
			echo "Ai ales timp de petrecut împreună. Mai jos găsești sumarul comenzii; te vom ține la curent cu livrarea.\n\n";
			return;
		}
		?>
		<div style="margin: 22px 0 28px; padding: 27px 30px; background-color: #173b2b; color: #fffaf0;">
			<p style="margin: 0 0 10px; color: #d4b77d; font-size: 12px; font-weight: 700; letter-spacing: 1.5px;">FAMILYROUND</p>
			<p style="margin: 0 0 10px; color: #fffaf0; font-family: Georgia, serif; font-size: 29px; line-height: 1.2;"><?php echo esc_html( $greeting ); ?></p>
			<p style="margin: 0; color: #f3eddf; font-size: 15px; line-height: 1.65;">Ai ales timp de petrecut împreună. Mai jos găsești sumarul comenzii; te vom ține la curent cu livrarea.</p>
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
		$link     = FR_Referrals::referral_link( $referrer->referral_code );
		$stats = FR_Referrals::public_stats_link( $referrer->referral_code );
		$map = add_query_arg( 'code', $referrer->referral_code, home_url( '/harta-familyround/' ) );
		$whatsapp = 'https://wa.me/?text=' . rawurlencode( self::share_text( $link ) );
		$mailto = 'mailto:?subject=' . rawurlencode( 'O idee pentru timpul petrecut împreună' ) . '&body=' . rawurlencode( self::share_text( $link ) );
		$units = FR_Orders::eligible_units( $order );
		$expected = $units * FR_Orders::SALE_AMOUNT;

		if ( $plain_text ) {
			echo "\n\n";
			if ( $contributed > 0 ) {
				echo "Din comanda ta am alocat {$contributed} lei în Pușculița FamilyRound. Suma este pusă deoparte de noi, fără cost în plus pentru tine.\n\n";
			} elseif ( $expected > 0 ) {
				echo "După confirmarea plății, vom pune deoparte {$expected} lei din comanda ta în Pușculița FamilyRound, fără cost în plus pentru tine.\n\n";
			}
			echo "Dă timpul mai departe: la prima comandă a fiecărui cumpărător venit prin linkul tău, noi mai adăugăm 5 lei pentru fiecare joc eligibil în Pușculiță. Codul tău poate ajunge la oricâte familii noi. Nu este un comision și nimeni nu plătește în plus.\n";
			echo "Codul tău: {$referrer->referral_code}\n";
			echo "Linkul tău: {$link}\n";
			echo "Trimite pe WhatsApp: {$whatsapp}\n";
			echo "Revino oricând să vezi recomandările validate și suma adăugată în Pușculiță: {$stats}\n";
			echo "Vezi pe hartă localitățile în care au fost cumpărate jocuri prin codul tău: {$map}\n";
			return;
		}
		?>
		<div style="margin: 30px 0; padding: 30px; background-color: #f5efe3; color: #173b2b;">
			<?php if ( $contributed > 0 ) : ?>
				<p style="margin: 0 0 18px; padding-bottom: 18px; border-bottom: 1px solid #d8cab0; font-size: 16px; line-height: 1.6;">
					<strong style="color: #173b2b; font-size: 24px;"><?php echo esc_html( number_format_i18n( $contributed, 0 ) ); ?> lei</strong> puși deoparte din comanda ta în Pușculița FamilyRound. Îi alocăm noi, fără cost în plus pentru tine.
				</p>
			<?php elseif ( $expected > 0 ) : ?>
				<p style="margin: 0 0 18px; padding-bottom: 18px; border-bottom: 1px solid #d8cab0; font-size: 15px; line-height: 1.6;">După confirmarea plății, vom pune deoparte <strong><?php echo esc_html( number_format_i18n( $expected, 0 ) ); ?> lei</strong> din comanda ta în Pușculiță, fără cost în plus pentru tine.</p>
			<?php endif; ?>
			<h2 style="margin: 0 0 10px; font-family: Georgia, serif; font-size: 27px; font-weight: normal; line-height: 1.2; color: #173b2b;">Dă timpul mai departe.</h2>
			<p style="margin: 0 0 16px; font-size: 15px; line-height: 1.6;">
				Poate cunoști o familie căreia i-ar prinde bine mai mult timp împreună. Trimite-i linkul tău: la prima ei comandă, FamilyRound mai pune <strong>5 lei</strong> pentru fiecare joc eligibil în Pușculiță. Poți recomanda oricâte familii noi; nimeni nu plătește în plus.
			</p>
			<p style="margin: 0 0 7px; font-size: 12px; font-weight: 700; letter-spacing: .04em;">CODUL TĂU: <?php echo esc_html( $referrer->referral_code ); ?></p>
			<p style="margin: 0 0 20px; padding: 13px; background-color: #fffaf0; overflow-wrap: anywhere; font-size: 14px;">
				<a href="<?php echo esc_url( $link ); ?>" style="color: #173b2b; font-weight: 600;"><?php echo esc_html( $link ); ?></a>
			</p>
			<p style="margin: 0 0 20px; font-size: 14px; line-height: 2.7;">
				<a href="<?php echo esc_url( $whatsapp ); ?>" style="display: inline-block; margin-right: 13px; padding: 10px 16px; background-color: #173b2b; color: #fffaf0; text-decoration: none; font-weight: 700;">Trimite pe WhatsApp</a>
				<a href="<?php echo esc_url( $mailto ); ?>" style="color: #173b2b; font-weight: 700;">Trimite pe email</a>
			</p>
			<p style="margin: 0; padding-top: 15px; border-top: 1px solid #d8cab0; color: #4c6253; font-size: 13px; line-height: 1.6;">Păstrează codul din acest email. Poți reveni oricând să vezi recomandările validate și suma pusă deoparte prin el: <a href="<?php echo esc_url( $stats ); ?>" style="color: #173b2b; font-weight: 700;">Vezi impactul codului →</a><br><a href="<?php echo esc_url( $map ); ?>" style="color: #173b2b; font-weight: 700;">Vezi pe hartă localitățile recomandărilor tale →</a></p>
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
		$whatsapp = 'https://wa.me/?text=' . rawurlencode( $message );
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
