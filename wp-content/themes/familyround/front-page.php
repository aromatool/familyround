<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$shop_url = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/magazin' );
?>

<section class="fr-hero">
	<div class="fr-container">
		<h1>Mai mult timp împreună.</h1>
		<p>30 de provocări în familie, gândite să vă apropie — nu doar un joc, ci un motiv să lăsați telefoanele deoparte pentru o seară.</p>
		<a class="fr-cta" href="<?php echo esc_url( $shop_url ); ?>">Comandă jocul — 111 lei</a>
	</div>
</section>

<section class="fr-piggybank">
	<div class="fr-container">
		<h2>Pușculița FamilyRound</h2>
		<div id="fr-piggybank-counter" data-fr-island="piggybank-counter"></div>
	</div>
</section>

<?php get_footer(); ?>
