<?php
/**
 * Customer processing order email — FamilyRound override.
 *
 * The default "Hi X, we've received your order" greeting is dropped on
 * purpose: FR_Email::add_order_introduction() (hooked on
 * woocommerce_email_before_order_table) already renders a complete, branded
 * greeting right before the order table, so this template only needs the
 * header/footer scaffolding and the order details themselves.
 *
 * @package FamilyRound
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email );

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );
