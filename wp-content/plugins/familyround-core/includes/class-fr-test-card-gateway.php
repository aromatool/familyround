<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stand-in for NETOPIA while no real (sandbox or live) account is connected.
 * Marks the order paid instantly, exactly like a successful card charge, so
 * the "livrare + card" path can be tested end to end. Registered only while
 * FR_PRELAUNCH is defined — delete this file once NETOPIA has real
 * credentials, no other cleanup needed.
 */
class FR_Test_Card_Gateway extends WC_Payment_Gateway {

	public function __construct() {
		$this->id                 = 'fr_test_card';
		$this->method_title       = 'Card (test local)';
		$this->method_description = 'Simulează o plată cu cardul reușită, pentru testare locală cât timp NETOPIA nu are cont real conectat. Nu procesează nicio plată reală.';
		$this->has_fields         = false;
		$this->title              = 'Card (mod test — simulează NETOPIA)';
		$this->description        = 'Plată simulată pentru testare. Comanda se marchează automat ca plătită.';
		$this->init_settings();
		$this->enabled = 'yes';
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		$order->add_order_note( 'Plată simulată local (test) — nu a trecut niciun bancă/procesator real.' );
		$order->payment_complete();
		if ( WC()->cart ) {
			WC()->cart->empty_cart();
		}
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}
}
