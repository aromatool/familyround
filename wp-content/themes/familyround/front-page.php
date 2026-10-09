<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$shop_url          = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/magazin' );
$images_url        = get_theme_file_uri( 'assets/images/' );
$first_product_url = $shop_url;

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
			<span class="fr-eyebrow">FamilyRound</span>
			<h1>Faceți lucruri <em>împreună</em>.<br>Păstrați ce rămâne.</h1>
			<p>Construim jocuri și inițiative care aduc familiile mai aproape — departe de ecrane, mai aproape unii de alții. Primul nostru joc e deja aici.</p>
			<div class="fr-hero-cta-block">
				<a class="fr-cta" href="#jocuri">Descoperă jocurile FamilyRound</a>
				<span class="fr-cta-subtext">De la 111 lei</span>
			</div>
			<ul class="fr-hero-micro">
				<li>Jocuri de familie</li>
				<li>Pușculița FamilyRound</li>
				<li>O comunitate care crește</li>
			</ul>
		</div>
		<div class="fr-hero-card">
			<img class="fr-hero-photo" src="<?php echo esc_url( $images_url . 'hero-atmosphere.jpg' ); ?>" width="1400" height="788" alt="O familie pe un deal, așezată pe o pătură, petrecând timp împreună în natură">
		</div>
	</div>
</section>

<div class="fr-ribbon">
	<div class="fr-container fr-ribbon-inner">
		<span>Din fiecare joc, <strong>5 lei</strong> merg în Pușculița FamilyRound.</span>
		<a href="<?php echo esc_url( home_url( '/pusculita-familyround/' ) ); ?>">Vezi cum →</a>
	</div>
</div>

<section class="fr-pillar fr-reveal" id="jocuri">
	<div class="fr-container fr-pillar-grid">
		<div class="fr-pillar-image">
			<img src="<?php echo esc_url( $images_url . 'hero-lifestyle.jpg' ); ?>" width="1200" height="800" alt="O familie jucând FamilyRound la masă, alegând un cartonaș cu provocări">
		</div>
		<div class="fr-pillar-text">
			<p class="fr-pillar-cap">Jocurile FamilyRound</p>
			<h2>30 de provocări în familie</h2>
			<p>Primul nostru joc: 30 de cartonașe cu provocări, un poster pentru amintiri, 111 lei. Alegeți un cartonaș, faceți provocarea împreună, păstrați amintirea.</p>
			<a class="fr-cta-ghost" href="<?php echo esc_url( $first_product_url ); ?>">Vezi jocul →</a>
		</div>
	</div>
</section>

<section class="fr-piggybank fr-reveal">
	<div class="fr-container fr-piggybank-grid">
		<div data-fr-island="piggybank-counter" data-fr-variant="compact"></div>
		<div class="fr-piggybank-text">
			<p class="fr-pillar-cap fr-pillar-cap--on-dark">Pușculița FamilyRound</p>
			<h2>Bucuria voastră merge mai departe</h2>
			<p>Din fiecare joc cumpărat, punem 5 lei deoparte pentru o cauză.</p>
			<div class="fr-piggy-formula">
				<div><strong>5 lei</strong><span>din fiecare joc</span></div>
				<div class="fr-piggy-formula-op">+</div>
				<div><strong>5 lei</strong><span>în plus, prin recomandare</span></div>
				<div class="fr-piggy-formula-op">=</div>
				<div><strong>10 lei</strong><span>pentru bine</span></div>
			</div>
			<a class="fr-cta-ghost fr-cta-ghost--on-dark" href="<?php echo esc_url( home_url( '/pusculita-familyround/' ) ); ?>">Descoperă Pușculița →</a>
		</div>
	</div>
</section>

<section class="fr-pillar fr-pillar--reverse fr-reveal">
	<div class="fr-container fr-pillar-grid">
		<div class="fr-pillar-text">
			<p class="fr-pillar-cap">Harta FamilyRound</p>
			<h2>Fiecare familie adaugă un oraș pe hartă</h2>
			<p>Urmărim cum crește comunitatea FamilyRound, oraș cu oraș — fără nicio adresă sau dată personală afișată, doar impactul vostru colectiv.</p>
			<a class="fr-cta-ghost" href="<?php echo esc_url( home_url( '/harta-familyround/' ) ); ?>">Vezi harta →</a>
		</div>
		<div class="fr-pillar-visual">
			<div data-fr-island="map-points" data-fr-variant="compact"></div>
		</div>
	</div>
</section>

<section class="fr-pillar fr-reveal">
	<div class="fr-container fr-pillar-grid">
		<div class="fr-pillar-visual fr-pillar-visual--refer" aria-hidden="true">
			<img class="fr-logo-watermark" src="<?php echo esc_url( $images_url . 'logo-icon.png' ); ?>" width="415" height="395" alt="">
		</div>
		<div class="fr-pillar-text">
			<p class="fr-pillar-cap">Dă timpul mai departe</p>
			<h2>Recomandați, și aduceți încă 5 lei în Pușculiță</h2>
			<p>Primiți un link personal. Fiecare familie nouă care cumpără prin el aduce un bonus suplimentar în Pușculița FamilyRound — fără cont, fără complicații.</p>
			<a class="fr-cta-ghost" href="<?php echo esc_url( home_url( '/da-timpul-mai-departe/' ) ); ?>">Află mai multe →</a>
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

<?php get_footer(); ?>
