<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$images_url  = get_theme_file_uri( 'assets/images/' );
$shop_url    = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/magazin/' );
$game_url    = $shop_url;
$game_price  = '';
$fr_games    = function_exists( 'wc_get_products' ) ? wc_get_products( array( 'limit' => -1, 'status' => 'publish' ) ) : array();
foreach ( $fr_games as $fr_game ) {
	if ( false !== stripos( $fr_game->get_name(), '30 de provocări' ) ) {
		$game_url   = get_permalink( $fr_game->get_id() );
		$game_price = $fr_game->get_price();
		break;
	}
}
$pusculita_url = home_url( '/pusculita-familyround/' );
?>

<main class="fr-story-page">

	<section class="fr-story-hero">
		<div class="fr-container fr-story-hero-grid">
			<div class="fr-story-hero-copy">
				<p class="fr-story-cap">Din familia noastră, pentru a voastră</p>
				<h1>Totul a început cu dorința de a fi mai aproape.</h1>
				<p class="fr-story-hero-lead">„Suntem Alexandru și Alexandra, părinții a trei copii. Și credem că cele mai frumoase lucruri pe care le putem oferi celor dragi sunt timpul, atenția și amintirile create împreună.”</p>
			</div>
			<figure class="fr-story-hero-figure">
				<img src="<?php echo esc_url( $images_url . 'story-family.jpg' ); ?>" width="1200" height="800" alt="Familia Maja privind împreună apusul, așezați pe stânci lângă mare" fetchpriority="high">
			</figure>
		</div>
	</section>

	<section class="fr-ourstory">
		<div class="fr-container fr-ourstory-grid">
			<figure class="fr-ourstory-figure">
				<img src="<?php echo esc_url( $images_url . 'together-laugh.jpg' ); ?>" width="880" height="1100" loading="lazy" alt="Familia Maja, zâmbind împreună la fermă">
				<figcaption>Momentele simple, cele care rămân.</figcaption>
			</figure>
			<div class="fr-ourstory-textcol">
				<p class="fr-story-cap">Cum a început</p>
				<div class="fr-ourstory-head">
					<h2>Nu ne doream încă un joc. Ne doream mai multe momente împreună.</h2>
				</div>
				<div class="fr-ourstory-body">
					<p>Printre zile aglomerate, responsabilități și planuri, ne-am dat seama cât de repede trece timpul. Copiii cresc, zilele se adună, iar momentele simple petrecute împreună devin amintirile la care ne întoarcem cu cel mai mult drag.</p>
					<p>Ne-am dorit să creăm ceva care să ne amintească să ne oprim din când în când din agitația zilnică. Să ne jucăm, să râdem, să descoperim lucruri noi și, mai ales, să fim prezenți unii pentru ceilalți.</p>
					<p>De aici s-a născut FamilyRound.</p>
				</div>
			</div>
		</div>
	</section>

	<section class="fr-story-bigquote">
		<div class="fr-container fr-story-bigquote-inner">
			<blockquote>Nu trebuie să fie perfect. Trebuie doar să fie al vostru.</blockquote>
			<p class="fr-story-bigquote-tag">Mai mult timp, împreună.</p>
		</div>
	</section>

	<section class="fr-story-product">
		<div class="fr-container fr-story-product-grid">
			<div class="fr-story-product-copy">
				<p class="fr-story-cap">Din idee, într-o cutie</p>
				<h2>Amintiri care rămân.</h2>
				<div class="fr-story-product-body">
					<p>Așa a apărut primul nostru joc: 30 de provocări care invită familiile să încerce lucruri noi, să râdă, să descopere și să petreacă timp împreună.</p>
					<p>Alături de provocări, posterul „Amintiri care rămân” devine locul în care fiecare fotografie, desen, bilețel sau mic suvenir își găsește locul într-o poveste unică.</p>
				</div>
				<a class="fr-cta-ghost" href="<?php echo esc_url( $game_url ); ?>">Descoperă primul nostru joc<?php echo $game_price ? ' · ' . esc_html( number_format( (float) $game_price, 0, ',', '.' ) ) . ' lei' : ''; ?> →</a>
			</div>
			<div class="fr-story-product-figures">
				<img class="fr-story-product-figure-box" src="<?php echo esc_url( $images_url . 'product/complete_product.webp' ); ?>" width="1447" height="1087" loading="lazy" alt="Cutia FamilyRound, cartonașele cu provocări și posterul „Amintiri care rămân” desfăcut">
				<img class="fr-story-product-figure-poster" src="<?php echo esc_url( $images_url . 'unbox/harta_perete2.png' ); ?>" width="1448" height="1086" loading="lazy" alt="Posterul „Amintiri care rămân”, completat cu fotografii și amintiri, agățat pe perete">
			</div>
		</div>
	</section>

	<section class="fr-story-vision">
		<div class="fr-container fr-story-vision-inner">
			<h2>FamilyRound este doar la început.</h2>
			<p>Visăm la o lume în care familiile își fac mai mult timp unele pentru celelalte. În care o seară obișnuită se poate transforma într-o aventură, iar o întrebare simplă poate începe o conversație de neuitat.</p>
			<p>Ne dorim să construim, pas cu pas, o colecție de jocuri și experiențe care aduc familiile mai aproape.</p>
		</div>
	</section>

	<section class="fr-story-giving">
		<div class="fr-container fr-story-giving-inner">
			<p class="fr-story-cap fr-story-cap--light">Bucuria merge mai departe</p>
			<h2>Bucuria crește când este împărtășită.</h2>
			<p>Credem că momentele frumoase merită să ajungă cât mai departe. De aceea, din fiecare joc cumpărat, 5 lei merg în Pușculița FamilyRound, o inițiativă prin care ne dorim să aducem bucurie și altor copii și familii.</p>
			<a class="fr-cta-ghost fr-cta-ghost--on-dark" href="<?php echo esc_url( $pusculita_url ); ?>">Descoperă Pușculița FamilyRound →</a>
		</div>
	</section>

	<section class="fr-story-closing">
		<div class="fr-container fr-story-closing-inner">
			<h2>Iar povestea continuă cu voi.</h2>
			<p>Fiecare familie care ne primește în casa ei adaugă un capitol nou poveștii FamilyRound.</p>
			<p>Povestea noastră abia începe. Și ne bucurăm că sunteți aici.</p>
			<p class="fr-story-sign">Cu drag,<br>Alexandru &amp; Alexandra</p>
		</div>
	</section>

</main>

<?php get_footer(); ?>
