<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$fr_product_title = get_the_title( get_queried_object_id() );
if ( false === stripos( $fr_product_title, '30 de provocări' ) ) {
	echo '<main class="fr-woocommerce fr-container">';
	while ( have_posts() ) {
		the_post();
		wc_get_template_part( 'content', 'single-product' );
	}
	echo '</main>';
	get_footer();
	return;
}

$u     = get_theme_file_uri( 'assets/images/' );
$unbox = $u . 'unbox/';

while ( have_posts() ) :
	the_post();
	global $product;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		$product = wc_get_product( get_the_ID() );
	}
	$price_html = $product ? $product->get_price_html() : '';
	$cart_url   = $product ? $product->add_to_cart_url() : '#fr-product-order';
	?>

<div class="fr-gamepage">

	<!-- 01 · HERO (split) -->
	<section class="gp-hero gp-sec" id="top">
		<div class="wrap gp-hero-grid">
			<div class="gp-hero-copy">
				<p class="gp-eyebrow rev">FamilyRound · 30 de provocări în familie</p>
				<h1 class="rev">30 de idei.<br>O mulțime de amintiri.</h1>
				<p class="gp-lead rev">Alegeți un cartonaș, faceți ceva împreună și păstrați momentul pe posterul vostru. Unele experiențe încep acasă. Altele vă duc într-o mică aventură.</p>
				<div class="gp-hero-buy rev" id="fr-product-order">
					<?php woocommerce_template_single_add_to_cart(); ?>
					<?php if ( $price_html ) : ?><span class="gp-hero-price"><?php echo wp_kses_post( $price_html ); ?></span><?php endif; ?>
				</div>
				<p class="gp-order-note rev">Alegeți câte seturi doriți. În coș le puteți verifica sau modifica înainte de livrare și plată.</p>
				<p class="gp-hero-meta rev">30 de provocări · Poster pentru amintiri</p>
				<a class="gp-link rev" href="#contine">Descoperă ce conține <span class="c">›</span></a>
			</div>
			<div class="gp-hero-media rev">
				<img src="<?php echo esc_url( $unbox . 'cutie_inchisa.png' ); ?>" width="1448" height="1086" alt="Cutia FamilyRound — 30 de provocări în familie" fetchpriority="high">
			</div>
		</div>
	</section>

	<!-- 02 · BRAND STATEMENT -->
	<section class="gp-statement gp-sec">
		<div class="wrap">
			<p class="gp-eyebrow gp-eyebrow--light rev">Despre ce este, de fapt</p>
			<h2 class="rev">Nu trebuie să faceți ceva extraordinar.<br><em>Doar să faceți ceva împreună.</em></h2>
			<p class="rev">O după-amiază fără planuri. O duminică acasă. Un drum cu trenul doar de dragul drumului. Uneori, tocmai lucrurile simple sunt cele despre care povestim ani mai târziu.</p>
		</div>
	</section>

	<!-- 03 · DESCHIDEȚI CUTIA -->
	<section class="gp-feature gp-sec" id="contine">
		<div class="wrap">
			<div class="gp-media rev"><img src="<?php echo esc_url( $unbox . 'cutie_plina.png' ); ?>" width="1426" height="1103" loading="lazy" alt="Cutia FamilyRound deschisă, cu teancul de cartonașe, washi tape și colțare foto"></div>
			<div class="gp-copy rev">
				<h2>Deschideți cutia.<br>De aici începe totul.</h2>
				<p>Înăuntru vă așteaptă 30 de provocări, fiecare cu o idee diferită de făcut împreună. Nu trebuie să vă pregătiți pentru toate. Alegeți una care vi se potrivește astăzi și vedeți unde vă duce.</p>
				<ul class="gp-incl">
					<li><span class="n">01</span><div><h3>30 de cartonașe cu provocări</h3><p>Fiecare, o idee diferită de făcut împreună.</p></div></li>
					<li><span class="n">02</span><div><h3>Posterul „Amintiri care rămân"</h3><p>Locul unde adunați, pe parcurs, urmele experiențelor.</p></div></li>
					<li><span class="n">03</span><div><h3>Accesorii pentru amintiri</h3><p>Washi tape și colțare foto negre, din carton.</p></div></li>
					<li><span class="n">04</span><div><h3>Cartonașe pentru notițe</h3><p>Pentru un gând, o dată sau un nume de păstrat.</p></div></li>
				</ul>
			</div>
		</div>
	</section>

	<!-- 04 · DESCOPERIȚI PROVOCĂRILE -->
	<section class="gp-fan-sec gp-sec gp-on-ivory">
		<div class="gp-fan-head wrap rev">
			<p class="gp-eyebrow">30 de idei de încercat</p>
			<h2>Nu știți ce să faceți weekendul acesta?<br>Alegeți un cartonaș.</h2>
			<p>Poate construiți un vehicul din lucrurile pe care le aveți acasă. Poate plecați cu trenul într-un loc nou. Sau poate refaceți o fotografie de familie de acum câțiva ani.</p>
		</div>
		<div class="gp-fan">
			<figure style="--i:-3;--d:3"><img src="<?php echo esc_url( $u . 'card-01.jpg' ); ?>" loading="lazy" alt="Drumeție în familie"></figure>
			<figure style="--i:-1;--d:1"><img src="<?php echo esc_url( $u . 'card-15.jpg' ); ?>" loading="lazy" alt="O excursie cu trenul"></figure>
			<figure style="--i:1;--d:1"><img src="<?php echo esc_url( $u . 'card-17.jpg' ); ?>" loading="lazy" alt="Construim un vehicul"></figure>
			<figure style="--i:3;--d:3"><img src="<?php echo esc_url( $u . 'card-25.jpg' ); ?>" loading="lazy" alt="Restaurant familie"></figure>
		</div>
		<p class="gp-fan-note rev">Descoperă câteva dintre cele 30 de provocări. Și acestea sunt doar începutul.</p>
	</section>

	<!-- 05 · POSTERUL AMINTIRILOR -->
	<section class="gp-poster gp-sec">
		<div class="wrap">
			<div class="gp-poster-head rev">
				<p class="gp-eyebrow">Amintiri care rămân</p>
				<h2>Provocările trec. Amintirile rămân.</h2>
				<p>După fiecare experiență, păstrați ceva pe poster. O fotografie, o frunză, un desen sau un bilet de la primul vostru drum cu trenul. Cu timpul, fiecare familie va avea un poster diferit. Pentru că fiecare familie are propria poveste.</p>
			</div>
			<div class="gp-beforeafter rev">
				<figure><img src="<?php echo esc_url( $unbox . 'poster.png' ); ?>" loading="lazy" alt="Posterul Amintiri care rămân, gol"><figcaption>La început</figcaption></figure>
				<figure><img src="<?php echo esc_url( $unbox . 'harta_plina.png' ); ?>" loading="lazy" alt="Posterul Amintiri care rămân, completat cu fotografii și amintiri"><figcaption>Pe parcurs</figcaption></figure>
			</div>
			<p class="gp-poster-end rev">La început este doar un poster. Apoi devine al vostru.</p>
		</div>
	</section>

	<!-- 06 · ÎN VIAȚA REALĂ -->
	<section class="gp-life gp-sec">
		<div class="wrap">
			<h2 class="rev">Cele mai frumoase povești încep de obicei cu „Hai să facem ceva".</h2>
			<div class="gp-life-grid rev">
				<figure class="wide"><img src="<?php echo esc_url( $unbox . 'lifestyle_masa.png' ); ?>" loading="lazy" alt="Cutia FamilyRound și posterul amintirilor pe masă, într-o casă reală"></figure>
				<figure class="tall"><img src="<?php echo esc_url( $u . 'together-try.jpg' ); ?>" loading="lazy" alt="Un părinte și copilul făcând o activitate împreună"></figure>
				<figure><img src="<?php echo esc_url( $unbox . 'cutie_maini.png' ); ?>" loading="lazy" alt="Cutia FamilyRound ținută în mâini"></figure>
			</div>
		</div>
	</section>

	<!-- 07 · UN CADOU PENTRU FAMILIE -->
	<section class="gp-gift gp-sec">
		<div class="wrap">
			<div class="gp-media rev"><img src="<?php echo esc_url( $unbox . 'cutie_detaliu.png' ); ?>" loading="lazy" alt="Detaliu cu logo-ul auriu pe cutia FamilyRound"></div>
			<div class="gp-copy rev">
				<h2>Ce le oferi unor oameni care au deja destule lucruri?</h2>
				<p>Poate nu au nevoie de încă un obiect. Poate le-ar prinde bine 30 de motive să iasă din rutină, să descopere locuri, să râdă și să adune câteva amintiri noi.</p>
				<a class="gp-pill" href="<?php echo esc_url( $cart_url ); ?>" data-fr-order-submit>Comandă acum</a>
			</div>
		</div>
	</section>

	<!-- 08 · DETALII ȘI FAQ -->
	<section class="gp-faq gp-sec gp-on-ivory">
		<div class="wrap gp-faq-grid">
			<div class="gp-faq-details rev">
				<h2>Detalii</h2>
				<ul>
					<li><span>Cartonașe</span><strong>30 de provocări</strong></li>
					<li><span>Poster</span><strong>„Amintiri care rămân", 50 × 40 cm</strong></li>
					<li><span>Cutie</span><strong>Rigidă, premium, închidere magnetică</strong></li>
					<li><span>Accesorii</span><strong>Washi tape, colțare foto, cartonașe notițe</strong></li>
					<li><span>Limba</span><strong>Română</strong></li>
				</ul>
			</div>
			<div class="gp-faq-list rev">
				<h2>Întrebări frecvente</h2>
				<div class="gp-acc">
					<details><summary>Trebuie să facem provocările în ordine?</summary><p>Nu. Puteți alege orice cartonaș, în funcție de timpul și cheful vostru.</p></details>
					<details><summary>În cât timp trebuie să terminăm cele 30 de provocări?</summary><p>Nu există termen limită. Le puteți face în ritmul familiei voastre.</p></details>
					<details><summary>Sunt toate provocările pentru acasă?</summary><p>Nu. Unele pot fi făcute acasă, iar altele propun ieșiri, explorări și mici excursii.</p></details>
					<details><summary>Putem repeta o provocare?</summary><p>Sigur. Puteți reveni la experiențele care v-au plăcut și le puteți transforma în tradiții.</p></details>
					<details><summary>Ce facem cu posterul?</summary><p>Păstrați pe el fotografii, desene, bilețele și mici amintiri ale provocărilor făcute împreună.</p></details>
					<details><summary>Ce conține exact cutia?</summary><p>30 de cartonașe cu provocări, posterul „Amintiri care rămân", accesorii pentru amintiri (washi tape și colțare foto) și cartonașe pentru notițe.</p></details>
				</div>
			</div>
		</div>
	</section>

	<!-- 09 · FINAL CTA -->
	<section class="gp-final gp-sec">
		<div class="wrap">
			<img class="gp-final-shot rev" src="<?php echo esc_url( $unbox . 'cutie_inchisa.png' ); ?>" loading="lazy" alt="Cutia FamilyRound">
			<h2 class="rev">Prima provocare poate începe chiar weekendul acesta.</h2>
			<p class="rev">30 de experiențe de făcut în ritmul vostru. Fără ordine obligatorie, fără punctaj și fără grabă.</p>
			<div class="rev gp-final-cta">
				<a class="gp-pill gp-pill--gold" href="<?php echo esc_url( $cart_url ); ?>" data-fr-order-submit>Comandă acum</a>
				<?php if ( $price_html ) : ?><span class="gp-final-price"><?php echo wp_kses_post( $price_html ); ?></span><?php endif; ?>
			</div>
		</div>
	</section>

</div>

<!-- sticky mobile buy bar -->
<div class="fr-gp-sticky" id="gpSticky">
	<span class="fr-gp-sticky-price"><?php echo wp_kses_post( $price_html ); ?></span>
	<a class="fr-gp-sticky-btn" href="<?php echo esc_url( $cart_url ); ?>" data-fr-order-submit>Comandă acum</a>
</div>

<?php
	// Product structured data — real data only, no fabricated ratings/reviews.
	$fr_ld = array(
		'@context' => 'https://schema.org/',
		'@type'    => 'Product',
		'name'     => wp_strip_all_tags( get_the_title() ),
		'image'    => array( $unbox . 'cutie_inchisa.png' ),
		'description' => '30 de idei pentru timp petrecut împreună. Alegeți un cartonaș, faceți provocarea și păstrați amintirile pe posterul FamilyRound.',
		'brand'    => array( '@type' => 'Brand', 'name' => 'FamilyRound' ),
	);
	if ( $product ) {
		$fr_ld['offers'] = array(
			'@type'         => 'Offer',
			'price'         => $product->get_price(),
			'priceCurrency' => get_woocommerce_currency(),
			'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
			'url'           => get_permalink(),
		);
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $fr_ld ) . '</script>';
	?>

<?php endwhile; ?>

<?php get_footer(); ?>
