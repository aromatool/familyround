<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$shop_url = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/magazin' );
?>

<section class="fr-hero fr-texture">
	<div class="fr-container fr-hero-grid">
		<div class="fr-hero-text">
			<span class="fr-eyebrow">30 de provocări în familie</span>
			<h1>Mai mult timp,<br>împreună.</h1>
			<p>Trageți un cartonaș. Faceți provocarea împreună — 10 minute, nu o seară întreagă. Bifați-o pe poster. Peste un an, aveți 30 de amintiri pe care nu le-ați fi avut altfel.</p>
			<a class="fr-cta" href="<?php echo esc_url( $shop_url ); ?>">Comandă jocul — 111 lei</a>
		</div>
		<div class="fr-hero-card">
			<div data-fr-island="signature-card"></div>
		</div>
	</div>
</section>

<section class="fr-how fr-reveal">
	<div class="fr-container">
		<span class="fr-eyebrow">Ce găsești în cutie</span>
		<h2>Cum funcționează</h2>
		<div class="fr-how-grid">
			<div class="fr-how-step">
				<span class="fr-how-number">01</span>
				<h3>Trageți un cartonaș</h3>
				<p>30 de provocări diferite — de la jocuri scurte la întrebări care chiar contează. Alegeți una la întâmplare sau pe cea care vi se potrivește azi.</p>
			</div>
			<div class="fr-how-step">
				<span class="fr-how-number">02</span>
				<h3>Faceți provocarea</h3>
				<p>Împreună, fără telefoane. Majoritatea durează 10-20 de minute — se potrivesc între cină și culcare, nu cer o seară întreagă liberă.</p>
			</div>
			<div class="fr-how-step">
				<span class="fr-how-number">03</span>
				<h3>Bifați-o pe poster</h3>
				<p>Posterul din cutie ține evidența provocărilor făcute. În timp, devine harta amintirilor voastre de familie — nu a unui joc oarecare.</p>
			</div>
		</div>
	</div>
</section>

<section class="fr-statement fr-reveal">
	<p>Peste câțiva ani, copiii nu vor ține minte emisiunea pe care ați văzut-o împreună. <em>Vor ține minte seara în care ați făcut ceva, împreună.</em></p>
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
