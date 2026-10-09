<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$shop_url = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/magazin' );
?>

<section class="fr-hero fr-texture">
	<div class="fr-container fr-hero-grid">
		<div class="fr-hero-text">
			<span class="fr-eyebrow">30 de provocări în familie</span>
			<h1>Mai mult timp,<br>împreună.</h1>
			<p>30 de provocări gândite să vă apropie — nu doar un joc, ci un motiv să lăsați telefoanele deoparte pentru o seară.</p>
			<a class="fr-cta" href="<?php echo esc_url( $shop_url ); ?>">Comandă jocul — 111 lei</a>
		</div>
		<div class="fr-hero-card">
			<div data-fr-island="signature-card"></div>
		</div>
	</div>
</section>

<section class="fr-statement fr-reveal">
	<p>30 de provocări. O seară fără telefoane. <em>O amintire care rămâne.</em></p>
</section>

<section class="fr-stats fr-reveal">
	<div class="fr-stats-inner">
		<div class="fr-stat">
			<div class="fr-stat-number">30</div>
			<div class="fr-stat-label">Provocări în cutie</div>
		</div>
		<div class="fr-stat">
			<div class="fr-stat-number">111 lei</div>
			<div class="fr-stat-label">Preț, livrare separată</div>
		</div>
		<div class="fr-stat">
			<div class="fr-stat-number">5 lei</div>
			<div class="fr-stat-label">Alocați Pușculiței, din fiecare joc</div>
		</div>
	</div>
</section>

<section class="fr-piggybank">
	<div class="fr-container">
		<span class="fr-eyebrow">Pușculița FamilyRound</span>
		<h2>Fiecare joc contează dublu</h2>
		<div data-fr-island="piggybank-counter"></div>
	</div>
</section>

<?php get_footer(); ?>
