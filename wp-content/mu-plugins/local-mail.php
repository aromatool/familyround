<?php
/**
 * Doar pentru dezvoltare locală: rutează toate emailurile către Mailpit
 * (http://localhost:8025), ca să le putem citi fără SMTP real configurat.
 * Nu are ce căuta pe producție — Cloudways/hostingul real va avea propriul SMTP.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'phpmailer_init', function ( $phpmailer ) {
	$phpmailer->isSMTP();
	$phpmailer->Host     = 'mailpit';
	$phpmailer->Port     = 1025;
	$phpmailer->SMTPAuth = false;
	$phpmailer->SMTPAutoTLS = false;
} );
