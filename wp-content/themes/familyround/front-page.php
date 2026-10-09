<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$shop_url = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/magazin' );
?>

<section class="fr-hero">
	<div class="fr-container fr-hero-inner">
		<div class="fr-hero-text">
			<span class="fr-eyebrow">30 de provocări în familie</span>
			<h1>Mai mult timp împreună.</h1>
			<p>30 de provocări gândite să vă apropie — nu doar un joc, ci un motiv să lăsați telefoanele deoparte pentru o seară.</p>
			<a class="fr-cta" href="<?php echo esc_url( $shop_url ); ?>">Comandă jocul — 111 lei</a>
		</div>
		<div class="fr-hero-visual" aria-hidden="true">
			<div class="fr-card fr-card--1">Priviți-vă în ochi 30 de secunde, fără să vorbiți</div>
			<div class="fr-card fr-card--2">Gătiți ceva nou, împreună</div>
			<div class="fr-card fr-card--3">Faceți un picnic în living</div>
		</div>
	</div>
</section>

<section class="fr-piggybank fr-reveal">
	<div class="fr-container">
		<span class="fr-eyebrow">Pușculița FamilyRound</span>
		<h2>Fiecare joc contează dublu</h2>
		<div style="margin: 32px 0;">
			<div data-fr-island="piggybank-counter"></div>
		</div>
	</div>
</section>

<?php get_footer(); ?>
