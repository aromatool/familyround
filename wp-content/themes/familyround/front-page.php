<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$shop_url   = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/magazin' );
$images_url = get_theme_file_uri( 'assets/images/' );

$fr_cards = array(
	1  => 'Drumeție',
	2  => 'Picnic',
	3  => 'Comoara din Natură',
	4  => 'Planta care Crește cu Voi',
	5  => 'Cu Cortul în Sufragerie',
	6  => 'Povești la Lumina Lanternei',
	7  => 'Inversați Rolul pentru o Zi',
	8  => 'Coșul cu Surprize',
	9  => 'Expediția cu Monedă',
	10 => 'Refaceți o Fotografie Veche',
	11 => 'Scrisoarea de Peste un An',
	12 => 'Amprenta Familiei',
	13 => 'Călătorie în Copilăria Părinților',
	14 => 'Misiunea Face Lumea mai Frumoasă',
	15 => 'O Excursie cu Trenul',
	16 => 'Surprindeți Răsăritul Împreună',
	17 => 'Construim un Vehicul',
	18 => 'Zbor cu Zmeie',
	19 => 'Arborele Genealogic',
	20 => 'O Zi la Planetariu sau Observator',
	21 => 'Ziua în Care Construiți Jucării',
	22 => 'Din Natură în Borcan',
	23 => 'Zi de Magie',
	24 => 'Construiți o Plută',
	25 => 'Restaurant Familie',
	26 => 'Lăsați Ceva în Urmă',
	27 => 'Vânătoare de Comori',
	28 => 'Faceți o Surpriză Bunicilor',
	29 => 'Karaoke',
	30 => 'Blazonul Familiei',
);
?>

<section class="fr-hero fr-texture">
	<div class="fr-container fr-hero-grid">
		<div class="fr-hero-text">
			<span class="fr-eyebrow">30 de provocări. 30 de amintiri.</span>
			<h1>Faceți lucruri împreună.<br>Păstrați ce rămâne.</h1>
			<p>Alegeți un cartonaș și porniți într-o mică aventură în familie. Unele provocări durează câteva minute, altele vă scot din casă. După fiecare, păstrați o fotografie sau o amintire pe poster. La final, nu rămâne doar un set de cartonașe. Rămâne povestea voastră.</p>
			<div class="fr-hero-cta-block">
				<a class="fr-cta" href="#toate-cele-30">Descoperă cele 30 de provocări</a>
				<span class="fr-cta-subtext">Set complet · 111 lei</span>
			</div>
			<ul class="fr-hero-micro">
				<li>30 de provocări</li>
				<li>Poster de amintiri</li>
				<li>Fără reguli complicate</li>
			</ul>
		</div>
		<div class="fr-hero-card">
			<div class="fr-card-stack-wrap">
				<img class="fr-card-decoy fr-card-decoy--1" src="<?php echo esc_url( $images_url . 'card-03.jpg' ); ?>" alt="" aria-hidden="true">
				<img class="fr-card-decoy fr-card-decoy--2" src="<?php echo esc_url( $images_url . 'card-02.jpg' ); ?>" alt="" aria-hidden="true">
				<div data-fr-island="signature-card"></div>
			</div>
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

<section class="fr-gallery fr-reveal" id="toate-cele-30">
	<div class="fr-container fr-gallery-head">
		<span class="fr-eyebrow">Toate cele 30</span>
		<h2>Unele sunt simple, altele vă trimit într-o aventură</h2>
	</div>
	<div class="fr-gallery-track">
		<?php foreach ( $fr_cards as $num => $title ) : ?>
			<figure class="fr-gallery-card">
				<img src="<?php echo esc_url( $images_url . 'card-' . str_pad( $num, 2, '0', STR_PAD_LEFT ) . '.jpg' ); ?>" alt="Cartonaș FamilyRound nr. <?php echo esc_attr( $num ); ?> — <?php echo esc_attr( $title ); ?>" loading="lazy">
			</figure>
		<?php endforeach; ?>
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

<section class="fr-story fr-reveal">
	<div class="fr-container">
		<span class="fr-eyebrow">Povestea noastră</span>
		<h2>De ce există FamilyRound</h2>
		<p class="fr-story-text">Am pornit de la o întrebare simplă: cum lăsăm telefoanele deoparte și petrecem timp real, împreună, ca familie? Am scris 30 de provocări, le-am testat cu propriile noastre familii, și am construit o cutie pe care chiar vrem s-o ținem pe masa din sufragerie.</p>
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
