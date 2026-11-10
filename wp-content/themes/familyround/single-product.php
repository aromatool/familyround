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

$fr_sample_cards = array(
	array(
		'src'   => $u . 'card-01.jpg',
		'alt'   => 'Familie la drumeție pe un traseu de munte',
		'num'   => '01',
		'title' => 'Drumeție în familie',
	),
	array(
		'src'   => $u . 'card-15.jpg',
		'alt'   => 'Familie privind peisajul pe geamul unui tren',
		'num'   => '15',
		'title' => 'O excursie cu trenul',
	),
	array(
		'src'   => $u . 'card-17.jpg',
		'alt'   => 'Tată și copii construind un vehicul din materiale reciclate',
		'num'   => '17',
		'title' => 'Construim un vehicul',
	),
	array(
		'src'   => $u . 'card-25.jpg',
		'alt'   => 'Familie gătind și servind masa împreună, ca la restaurant',
		'num'   => '25',
		'title' => 'Restaurant familie',
	),
	array(
		'src'   => $u . 'card-02.jpg',
		'alt'   => 'Familie la picnic lângă un lac',
		'num'   => '02',
		'title' => 'Picnic în familie',
	),
	array(
		'src'   => $u . 'card-29.jpg',
		'alt'   => 'Familie cântând karaoke în sufragerie',
		'num'   => '29',
		'title' => 'Karaoke',
	),
);

while ( have_posts() ) :
	the_post();
	global $product;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		$product = wc_get_product( get_the_ID() );
	}
	$price_html = $product ? $product->get_price_html() : '';
	$cart_url   = ( fr_is_prelaunch() || ! $product ) ? '#fr-product-order' : $product->add_to_cart_url();
	?>

<div class="fr-gamepage">

	<!-- 01 · HERO (split) -->
	<section class="gp-hero gp-sec" id="top">
		<div class="wrap gp-hero-grid">
			<div class="gp-hero-copy">
				<p class="gp-eyebrow rev">FamilyRound · 30 de provocări în familie</p>
				<h1 class="rev">30 de idei.<br>O mulțime de amintiri.</h1>
				<p class="gp-lead rev">Alegeți un cartonaș, faceți ceva împreună cu copiii și păstrați momentul pe posterul vostru. Unele experiențe încep acasă. Altele vă duc într-o mică aventură.</p>
				<div class="gp-hero-buy rev" id="fr-product-order">
					<?php if ( fr_is_prelaunch() ) : ?>
						<form class="fr-prelaunch-form" data-fr-prelaunch-form data-product-id="<?php echo esc_attr( $product ? $product->get_id() : 0 ); ?>" data-endpoint="<?php echo esc_url( rest_url( 'familyround/v1/prelaunch-lead' ) ); ?>">
							<label class="fr-visually-hidden" for="fr-prelaunch-email">Adresa ta de email</label>
							<input type="email" id="fr-prelaunch-email" name="email" placeholder="Adresa ta de email" required>
							<button type="submit">Anunță-mă când pot comanda</button>
						</form>
						<p class="fr-prelaunch-note" data-fr-prelaunch-note hidden>Mulțumim! Te anunțăm imediat ce poți comanda.</p>
						<?php if ( $price_html ) : ?><span class="gp-hero-price"><?php echo wp_kses_post( $price_html ); ?></span><?php endif; ?>
					<?php else : ?>
						<?php woocommerce_template_single_add_to_cart(); ?>
						<?php if ( $price_html ) : ?><span class="gp-hero-price"><?php echo wp_kses_post( $price_html ); ?></span><?php endif; ?>
					<?php endif; ?>
				</div>
				<p class="gp-order-note rev"><?php echo fr_is_prelaunch() ? 'Lansăm în curând. Lasă-ți emailul și te anunțăm imediat ce poți comanda.' : 'Alegeți câte seturi doriți. În coș le puteți verifica sau modifica înainte de livrare și plată.'; ?></p>
				<p class="gp-hero-meta rev">30 de provocări · Poster pentru amintiri</p>
				<a class="gp-link rev" href="#contine">Descoperă ce conține <span class="c">›</span></a>
			</div>
			<div class="gp-hero-media rev">
				<img src="<?php echo esc_url( $u . 'product/complete_product.webp' ); ?>" width="1447" height="1087" alt="Cutia FamilyRound, cartonașele cu provocări, posterul pentru amintiri desfăcut și accesoriile incluse" fetchpriority="high">
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
			<div class="gp-media rev"><img src="<?php echo esc_url( $unbox . 'unbox-real.jpg' ); ?>" width="1400" height="1727" loading="lazy" alt="Cutia FamilyRound deschisă pe o masă de lemn, printre frunze de toamnă, cu teancul de cartonașe și scrisoarea de bun venit"></div>
			<div class="gp-copy rev">
				<h2>Deschideți cutia.<br>De aici începe totul.</h2>
				<p>Înăuntru vă așteaptă 30 de provocări, fiecare cu o idee diferită de făcut împreună. Nu trebuie să vă pregătiți pentru toate. Alegeți una care vi se potrivește astăzi și vedeți unde vă duce.</p>
				<ul class="gp-incl">
					<li><span class="n">01</span><div><h3>30 de cartonașe cu provocări</h3><p>De la o drumeție, până la o seară de poveste în sufragerie. Alegeți una — nu trebuie să le faceți în ordine.</p></div></li>
					<li><span class="n">02</span><div><h3>Posterul „Amintiri care rămân"</h3><p>50 × 40 cm, gândit să rămână pe perete. Lipiți o fotografie, o frunză, un bilet — pe parcurs devine harta amintirilor voastre.</p></div></li>
					<li><span class="n">03</span><div><h3>Accesorii pentru amintiri</h3><p>Washi tape, colțare foto și cartonașe pentru notițe — tot ce vă trebuie ca să lipiți o amintire, nu doar s-o păstrați într-un sertar.</p></div></li>
				</ul>
			</div>
		</div>
	</section>

	<!-- 04 · DESCOPERIȚI PROVOCĂRILE -->
	<section class="gp-fan-sec gp-sec gp-on-ivory">
		<div class="gp-fan-head wrap rev">
			<p class="gp-eyebrow">30 de idei de încercat</p>
			<h2>Nu știți ce să faceți weekendul acesta?<br>Alegeți un cartonaș.</h2>
			<p>Poate construiți un vehicul din lucrurile pe care le aveți acasă. Poate plecați cu trenul într-un loc nou. Sau poate refaceți o fotografie de familie de acum câțiva ani. Unele provocări durează 10 minute, altele o după-amiază întreagă — unele se fac acasă, altele vă scot din casă.</p>
		</div>
		<div class="gp-fan rev">
			<?php foreach ( $fr_sample_cards as $i => $fr_card ) : ?>
			<figure class="gp-fan-card" style="--r:<?php echo esc_attr( array( -6, 3, -3, 7, -5, 4 )[ $i ] ?? 0 ); ?>deg;--ty:<?php echo esc_attr( array( 6, -4, 8, -2, 5, -3 )[ $i ] ?? 0 ); ?>px">
				<img src="<?php echo esc_url( $fr_card['src'] ); ?>" loading="lazy" alt="<?php echo esc_attr( $fr_card['alt'] ); ?>">
			</figure>
			<?php endforeach; ?>
		</div>
		<p class="gp-fan-note rev">Fiecare cutie conține 30 de cartonașe ca acestea.</p>
	</section>

	<!-- 05 · POSTERUL AMINTIRILOR -->
	<section class="gp-poster gp-sec">
		<div class="wrap">
			<div class="gp-poster-head rev">
				<p class="gp-eyebrow">Amintiri care rămân</p>
				<h2>Provocările trec. Amintirile rămân.</h2>
				<p>După fiecare experiență, păstrați ceva pe poster. O fotografie, o frunză, un desen sau un bilet de la primul vostru drum cu trenul. Cu timpul, fiecare familie va avea un poster diferit. Pentru că fiecare familie are propria poveste.</p>
			</div>
			<div class="gp-reveal rev" style="--pct:50%">
				<div class="gp-reveal-frame">
					<img class="gp-reveal-img gp-reveal-before" src="<?php echo esc_url( $unbox . 'harta_perete_gola.png' ); ?>" loading="lazy" alt="Posterul Amintiri care rămân, gol, agățat pe perete">
					<img class="gp-reveal-img gp-reveal-after" src="<?php echo esc_url( $unbox . 'harta_perete2.png' ); ?>" loading="lazy" alt="Posterul Amintiri care rămân, completat cu fotografii și amintiri, agățat pe perete">
					<div class="gp-reveal-line" aria-hidden="true"></div>
				</div>
				<p class="gp-reveal-hint">Urmăriți cum se umple posterul sau glisați singuri</p>
				<div class="gp-reveal-control">
					<span class="gp-reveal-label">La început</span>
					<input type="range" class="gp-reveal-range" min="0" max="100" value="50" aria-label="Glisează pentru a vedea cum se umple posterul în timp">
					<span class="gp-reveal-label">Pe parcurs</span>
				</div>
			</div>
			<p class="gp-poster-end rev">La început este doar un poster. Apoi devine al vostru.</p>
		</div>
	</section>

	<!-- 07 · UN CADOU PENTRU FAMILIE -->
	<section class="gp-gift gp-sec">
		<div class="wrap">
			<div class="gp-media rev"><img src="<?php echo esc_url( $unbox . 'gift-box-real.jpg' ); ?>" width="1447" height="1930" loading="lazy" alt="Cutia FamilyRound închisă, cu logo auriu, așezată printre frunze de toamnă"></div>
			<div class="gp-copy rev">
				<h2>Ce le oferi unor oameni care au deja destule lucruri?</h2>
				<p>Poate nu au nevoie de încă un obiect. Poate le-ar prinde bine 30 de motive să iasă din rutină, să descopere locuri, să râdă și să adune câteva amintiri noi.</p>
				<p>Cutia rigidă, cu închidere magnetică și logo auriu, e gândită să stea la vedere — pe un raft de bibliotecă, nu într-un sertar.</p>
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
					<details><summary>Cum montăm posterul pe perete?</summary><p>Posterul vine împăturit în cutie. Înainte să-l montați, lăsați-l întins 24 de ore sub ceva greu — o carte groasă sau un covor — ca să se aplatizeze complet. După aceea, îl puteți prinde într-o ramă sau îl puteți lipi direct pe perete cu washi tape și puncte adezive, ca pe o ramă improvizată. Washi tape nu lasă urme pe perete.</p></details>
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
		'image'    => array( $u . 'product/complete_product.webp' ),
		'description' => '30 de idei pentru timp petrecut împreună, pentru familii cu copii. Alegeți un cartonaș, faceți provocarea și păstrați amintirile pe posterul FamilyRound.',
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
