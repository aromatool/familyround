<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$shop_url           = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/magazin' );
$images_url         = get_theme_file_uri( 'assets/images/' );
$first_product_url  = $shop_url;

if ( function_exists( 'wc_get_products' ) ) {
	$fr_products = wc_get_products( array( 'limit' => 1, 'orderby' => 'date', 'order' => 'ASC', 'status' => 'publish' ) );
	if ( ! empty( $fr_products ) ) {
		$first_product_url = get_permalink( $fr_products[0]->get_id() );
	}
}
?>

<section class="fr-hero fr-texture">
	<div class="fr-container fr-hero-grid">
		<div class="fr-hero-text">
			<span class="fr-eyebrow">30 de provocări. 30 de amintiri.</span>
			<h1>Faceți lucruri împreună.<br>Păstrați ce rămâne.</h1>
			<p>Alegeți un cartonaș și porniți într-o mică aventură în familie. Unele provocări durează câteva minute, altele vă scot din casă. După fiecare, păstrați o fotografie sau o amintire pe poster. La final, nu rămâne doar un set de cartonașe. Rămâne povestea voastră.</p>
			<div class="fr-hero-cta-block">
				<a class="fr-cta" href="<?php echo esc_url( $first_product_url ); ?>">Descoperă primul nostru joc</a>
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
	<div class="fr-container fr-how-inner">
		<h2>Fără puncte, fără câștigători,<br>fără reguli complicate.</h2>
		<div class="fr-how-list">
			<div class="fr-how-row">
				<span class="fr-how-num">01</span>
				<div class="fr-how-copy">
					<h3>Alegeți un cartonaș</h3>
					<p>Deschideți cutia și alegeți una dintre provocări. Nu trebuie făcute într-o anumită ordine și nu există un termen limită.</p>
				</div>
			</div>
			<div class="fr-how-row">
				<span class="fr-how-num">02</span>
				<div class="fr-how-copy">
					<h3>Faceți provocarea</h3>
					<p>Citiți cartonașul, pregătiți ce aveți nevoie și trăiți experiența împreună — acasă sau într-o mică aventură afară.</p>
				</div>
			</div>
			<div class="fr-how-row">
				<span class="fr-how-num">03</span>
				<div class="fr-how-copy">
					<h3>Păstrați amintirea</h3>
					<p>Notați data și lipiți pe poster o fotografie, un bilet sau un desen. Încet, posterul gol devine povestea familiei voastre.</p>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="fr-box fr-reveal">
	<div class="fr-container fr-box-grid">
		<div class="fr-box-image">
			<img src="<?php echo esc_url( $images_url . 'product-box.jpg' ); ?>" alt="Cutia FamilyRound cu cartonașele de provocări">
		</div>
		<div class="fr-box-text">
			<p class="fr-box-cap">Primul nostru joc</p>
			<h2>30 de provocări în familie</h2>
			<p class="fr-box-desc">30 de cartonașe cu provocări, un poster pentru amintiri și instrucțiuni simple — totul într-o cutie gândită să stea pe masa din sufragerie, nu într-un sertar.</p>
			<ul class="fr-box-facts">
				<li><strong>30</strong> provocări</li>
				<li><strong>111 lei</strong> setul complet</li>
				<li><strong>5 lei</strong> în Pușculiță, din fiecare joc</li>
			</ul>
			<a class="fr-cta" href="<?php echo esc_url( $first_product_url ); ?>">Vezi detaliile jocului</a>
		</div>
	</div>
</section>

<section class="fr-story fr-reveal">
	<div class="fr-container fr-story-inner">
		<p class="fr-story-cap">Povestea noastră</p>
		<p class="fr-story-quote">Am pornit de la o întrebare simplă: cum lăsăm telefoanele deoparte și petrecem timp real, împreună, ca familie?</p>
		<p class="fr-story-text">Am scris 30 de provocări, le-am testat cu propriile noastre familii, și am construit o cutie pe care chiar vrem s-o ținem pe masa din sufragerie. E primul nostru joc — nu ultimul.</p>
		<p class="fr-story-sign">— echipa FamilyRound</p>
	</div>
</section>

<section class="fr-statement fr-dark fr-reveal">
	<p>Peste câțiva ani, copiii nu vor ține minte emisiunea pe care ați văzut-o împreună. <em>Vor ține minte seara în care ați făcut ceva, împreună.</em></p>
</section>

<section class="fr-piggybank">
	<div class="fr-container">
		<h2>Fiecare joc contează dublu. 5 lei din fiecare joc ajung în Pușculița FamilyRound.</h2>
		<div data-fr-island="piggybank-counter"></div>
	</div>
</section>

<?php get_footer(); ?>
