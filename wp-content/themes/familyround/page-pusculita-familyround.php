<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<section class="fr-piggy-hero">
	<div class="fr-container fr-piggy-hero-inner">
		<h1>Bucuria voastră merge mai departe</h1>
		<p>Pentru fiecare joc FamilyRound cumpărat, punem noi 5 lei deoparte pentru o cauză. La prima comandă a unui cumpărător venit prin recomandare, mai adăugăm 5 lei pentru fiecare joc eligibil. La comenzile următoare rămân cei 5 lei obișnuiți per joc. Verificăm dacă este prima comandă după email sau telefon. Contribuția este suportată de noi, fără cost suplimentar pentru cumpărător.</p>
	</div>
</section>

<section class="fr-piggybank">
	<div class="fr-container fr-piggybank-grid">
		<div data-fr-island="piggybank-counter"></div>
		<div class="fr-piggybank-text">
			<h2>Mecanismul, pas cu pas</h2>
			<div class="fr-piggy-formula">
				<div><strong>5 lei</strong><span>pentru fiecare joc eligibil</span></div>
				<div class="fr-piggy-formula-op">+</div>
				<div><strong>5 lei</strong><span>în plus per joc, la prima comandă recomandată</span></div>
				<div class="fr-piggy-formula-op">=</div>
				<div><strong>10 lei</strong><span>alocați per joc din acea primă comandă</span></div>
			</div>
			<p>Suma alocată apare mai jos, calculată din comenzi reale. O vom actualiza pe măsură ce sumele sunt transferate efectiv cauzei active.</p>
		</div>
	</div>
</section>

<?php get_footer(); ?>
