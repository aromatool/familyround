<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$shop_url   = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/magazin' );
$images_url = get_theme_file_uri( 'assets/images/' );
?>

<section class="fr-hero fr-texture">
	<div class="fr-container fr-hero-grid">
		<div class="fr-hero-text">
			<span class="fr-eyebrow">30 de idei care vă scot din rutină</span>
			<h1>Mai mult timp,<br>împreună.</h1>
			<p>Alegeți un cartonaș. Faceți provocarea împreună — unele acasă, în 10 minute, altele vă trimit afară, într-o mică aventură. Păstrați momentul pe posterul familiei. Încet, posterul gol devine povestea voastră.</p>
			<a class="fr-cta" href="<?php echo esc_url( $shop_url ); ?>">Comandă jocul — 111 lei</a>
		</div>
		<div class="fr-hero-card">
			<div data-fr-island="signature-card"></div>
		</div>
	</div>
</section>

<section class="fr-how fr-reveal">
	<div class="fr-container">
		<span class="fr-eyebrow">Cum funcționează</span>
		<h2>Fără puncte, fără câștigători, fără reguli complicate</h2>
		<div class="fr-how-grid">
			<div class="fr-how-step">
				<span class="fr-how-number">01</span>
				<h3>Alegeți un cartonaș</h3>
				<p>Deschideți cutia și alegeți una dintre cele 30 de provocări. Nu trebuie făcute într-o anumită ordine și nu există un termen limită.</p>
			</div>
			<div class="fr-how-step">
				<span class="fr-how-number">02</span>
				<h3>Faceți provocarea</h3>
				<p>Citiți cartonașul, pregătiți ce aveți nevoie și trăiți experiența împreună — acasă sau într-o mică aventură afară.</p>
			</div>
			<div class="fr-how-step">
				<span class="fr-how-number">03</span>
				<h3>Păstrați amintirea</h3>
				<p>Notați data și lipiți pe poster o fotografie, un bilet sau un desen. Încet, posterul gol devine povestea familiei voastre.</p>
			</div>
		</div>
	</div>
</section>

<section class="fr-examples fr-reveal">
	<div class="fr-container">
		<span class="fr-eyebrow">Trei din cele 30</span>
		<h2>Unele sunt simple, altele vă trimit într-o aventură</h2>
		<div class="fr-examples-grid">
			<figure class="fr-example-card">
				<img src="<?php echo esc_url( $images_url . 'card-19-arbore.jpg' ); ?>" alt="Cartonaș FamilyRound — Arborele genealogic">
			</figure>
			<figure class="fr-example-card">
				<img src="<?php echo esc_url( $images_url . 'card-11-scrisoare.jpg' ); ?>" alt="Cartonaș FamilyRound — Scrisoarea de peste un an">
			</figure>
			<figure class="fr-example-card">
				<img src="<?php echo esc_url( $images_url . 'card-29-karaoke.jpg' ); ?>" alt="Cartonaș FamilyRound — Karaoke">
			</figure>
		</div>
	</div>
</section>

<section class="fr-box fr-reveal">
	<div class="fr-container fr-box-grid">
		<div class="fr-box-image">
			<img src="<?php echo esc_url( $images_url . 'product-box.jpg' ); ?>" alt="Cutia FamilyRound cu cartonașele de provocări">
		</div>
		<div class="fr-box-text">
			<span class="fr-eyebrow">Ce găsești în cutie</span>
			<h2>Un set complet, gândit pentru 30 de momente speciale</h2>
			<ul class="fr-box-list">
				<li><strong>30 de cartonașe cu provocări</strong> — fiecare propune o experiență diferită, cu instrucțiuni simple și idei de la care să porniți.</li>
				<li><strong>Posterul „Amintiri care rămân”</strong> — locul unde adunați, pe parcurs, fotografii, bilețele și urmele celor 30 de experiențe.</li>
				<li><strong>Instrucțiuni de început</strong> — tot ce trebuie să știți, fără pregătiri complicate și fără reguli de memorat.</li>
			</ul>
			<a class="fr-cta" href="<?php echo esc_url( $shop_url ); ?>">Comandă acum — 111 lei</a>
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
