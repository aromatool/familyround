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

<section class="fr-hero">
	<img class="fr-hero-photo" src="<?php echo esc_url( $images_url . 'hero-atmosphere.jpg' ); ?>" width="1400" height="788" alt="O familie pe un deal, așezată pe o pătură, petrecând timp împreună în natură">

	<div class="fr-container fr-hero-inner">
		<div class="fr-hero-text">
			<span class="fr-eyebrow">Jocuri pentru timpul care contează</span>
			<h1>Mai mult timp,<br><em>împreună.</em></h1>
			<p>Jocuri și experiențe care aduc familia împreună.</p>
			<a class="fr-cta" href="#jocuri">Descoperă experiențele →</a>
		</div>
	</div>
</section>

<div class="fr-ribbon">
	<div class="fr-container fr-ribbon-inner">
		<p>Din fiecare joc cumpărat, <strong>5 lei</strong> merg în Pușculița FamilyRound.</p>
		<a href="<?php echo esc_url( home_url( '/pusculita-familyround/' ) ); ?>">Descoperă Pușculița →</a>
	</div>
</div>

<?php
// Collection — built from real products only; no demo products.
$fr_games = array();
if ( function_exists( 'wc_get_products' ) ) {
	$fr_games = wc_get_products( array(
		'limit'   => -1,
		'orderby' => 'date',
		'order'   => 'ASC',
		'status'  => 'publish',
	) );
}

// Simple how-to-play for the game presentation.
$fr_steps = array(
	array( 'title' => 'Alegeți o provocare', 'text' => 'Una nouă oricând. Fără reguli, fără final.' ),
	array( 'title' => 'Trăiți-o împreună', 'text' => 'Bucurați-vă de fiecare clipă petrecută împreună.' ),
	array( 'title' => 'Lăsați o urmă pe perete', 'text' => 'Un desen, o poză, o frunză — pe harta amintirilor.' ),
);

$fr_specs = array(
	array( 'num' => '30', 'label' => 'provocări în familie' ),
	array( 'num' => '1', 'label' => 'hartă a amintirilor' ),
	array( 'num' => '∞', 'label' => 'momente de adăugat' ),
);

$fr_game_desc = 'O cutie premium, de pus în bibliotecă sau de dăruit. 30 de provocări care aduc familia mai aproape — iar tot ce rămâne în urma lor (un desen, o frunză, o poză) prinde pe harta amintirilor: posterul care, moment cu moment, devine tabloul familiei voastre.';

$fr_items = array();
foreach ( $fr_games as $index => $game ) {
	// Clean display title: strip a leading "FamilyRound –/-" brand prefix if present.
	$name  = $game->get_name();
	$title = preg_replace( '/^\s*FamilyRound\s*[–—\-]\s*/u', '', $name );

	// Media: prefer the product's own images, fall back to the brand concept shots.
	$set_img  = $images_url . 'hero-lifestyle.jpg';
	$set_alt  = 'Cutia „' . $title . '”, cartonașele și posterul FamilyRound, pe masa din sufragerie';
	if ( has_post_thumbnail( $game->get_id() ) ) {
		$thumb   = wp_get_attachment_image_src( get_post_thumbnail_id( $game->get_id() ), 'large' );
		$set_img = $thumb ? $thumb[0] : $set_img;
	}

	$gallery_ids = $game->get_gallery_image_ids();
	$peek_img    = $images_url . 'product-box.jpg';
	$peek_alt    = 'Cutia FamilyRound deschisă, cu un cartonaș de provocare la vedere';
	if ( ! empty( $gallery_ids ) ) {
		$g        = wp_get_attachment_image_src( $gallery_ids[0], 'large' );
		$peek_img = $g ? $g[0] : $peek_img;
	}

	// Short description from product data, else the approved (catchier) default copy.
	$desc = trim( wp_strip_all_tags( $game->get_short_description() ) );
	if ( '' === $desc ) {
		$desc = $fr_game_desc;
	}

	$fr_items[] = array(
		'label'      => 'De făcut împreună',
		'title'      => $title,
		'desc'       => $desc,
		'specs'      => $fr_specs,
		// Price lives on the product page, not in the homepage presentation.
		'price_html' => '',
		'url'        => get_permalink( $game->get_id() ),
		'set_img'    => $set_img,
		'set_alt'    => $set_alt,
		'peek_img'   => $peek_img,
		'peek_alt'   => $peek_alt,
		'peek_label' => 'Aruncă o privire în cutie',
		'back_label' => 'Înapoi la set',
		'steps'      => $fr_steps,
	);
}

// Fallback for a fresh install with no products yet — the one confirmed game.
if ( empty( $fr_items ) ) {
	$fr_items[] = array(
		'label'      => 'De făcut împreună',
		'title'      => '30 de provocări în familie',
		'desc'       => $fr_game_desc,
		'specs'      => $fr_specs,
		'price_html' => '',
		'url'        => $first_product_url,
		'set_img'    => $images_url . 'hero-lifestyle.jpg',
		'set_alt'    => 'Cutia FamilyRound, cartonașele și posterul, pe masa din sufragerie',
		'peek_img'   => $images_url . 'product-box.jpg',
		'peek_alt'   => 'Cutia FamilyRound deschisă, cu un cartonaș de provocare la vedere',
		'peek_label' => 'Aruncă o privire în cutie',
		'back_label' => 'Înapoi la set',
		'steps'      => $fr_steps,
	);
}
?>

<section class="fr-collection" id="jocuri">
	<div class="fr-container fr-container--wide">
		<div class="fr-collection-intro fr-fade-up">
			<p class="fr-collection-label">Experiențele FamilyRound</p>
			<h2>Cu ce începe povestea voastră?</h2>
			<p>Alege un început. Restul îl scrieți împreună.</p>
		</div>

		<?php foreach ( $fr_items as $fr_item ) : ?>
			<?php get_template_part( 'template-parts/collection-item', null, $fr_item ); ?>
		<?php endforeach; ?>

		<p class="fr-collection-closing fr-fade-up">Un joc fără reguli și fără final — doar cercul vostru, adunat din nou. Iar din fiecare cutie, <a href="<?php echo esc_url( home_url( '/pusculita-familyround/' ) ); ?>">5 lei merg mai departe</a>, către familii mai puțin norocoase.</p>
	</div>
</section>

<?php
// Brand intentions — three ways to be together. Replace a photo by swapping its file,
// edit the copy here directly; photos are decorative (not links).
$fr_together = array(
	array(
		'img'   => 'together-try.jpg',
		'w'     => 1100,
		'h'     => 825,
		'pos'   => '55% 42%',
		'alt'   => 'O mamă și o fetiță modelează împreună un bol din lut la masă.',
		'title' => 'Să încercăm ceva nou.',
		'text'  => 'Cu loc pentru încercări și surprize.',
		'col'   => 'fr-together-col--wide',
	),
	array(
		'img'   => 'together-listen.jpg',
		'w'     => 880,
		'h'     => 1100,
		'pos'   => '50% 35%',
		'alt'   => 'O mamă ascultă cu atenție un băiat care îi povestește ceva.',
		'title' => 'Să ne ascultăm mai mult.',
		'text'  => 'Cu răbdare și curiozitate.',
		'col'   => 'fr-together-col--mid',
	),
	array(
		'img'   => 'together-laugh.jpg',
		'w'     => 880,
		'h'     => 1100,
		'pos'   => '50% 35%',
		'alt'   => 'O familie râzând împreună pe covor, într-o cameră luminoasă.',
		'title' => 'Să râdem împreună.',
		'text'  => 'Fără să ne iasă totul perfect.',
		'col'   => '',
	),
);
?>
<section class="fr-together">
	<div class="fr-container fr-fade-up">
		<div class="fr-together-head">
			<div class="fr-together-headline">
				<p class="fr-collection-label">Mai multe feluri de a fi împreună</p>
				<h2>Voi cum vreți să fiți împreună?</h2>
			</div>
			<p class="fr-together-intro">Uneori, o idee nouă. Alteori, o conversație sau un motiv de râs.</p>
		</div>

		<div class="fr-together-grid">
			<?php foreach ( $fr_together as $t ) : ?>
				<div class="fr-together-col <?php echo esc_attr( $t['col'] ); ?>">
					<figure class="fr-together-figure">
						<img src="<?php echo esc_url( $images_url . $t['img'] ); ?>" width="<?php echo esc_attr( $t['w'] ); ?>" height="<?php echo esc_attr( $t['h'] ); ?>" loading="lazy" style="object-position: <?php echo esc_attr( $t['pos'] ); ?>" alt="<?php echo esc_attr( $t['alt'] ); ?>">
					</figure>
					<h3 class="fr-together-title"><?php echo esc_html( $t['title'] ); ?></h3>
					<p class="fr-together-text"><?php echo esc_html( $t['text'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>

		<p class="fr-together-closing">Pentru momentele acestea creăm FamilyRound.</p>
	</div>
</section>

<section class="fr-piggybank">
	<div class="fr-container fr-piggybank-grid">
		<div class="fr-piggybank-textcol fr-fade-up">
			<div class="fr-piggybank-intro-block">
				<p class="fr-pillar-cap fr-pillar-cap--on-dark">Pușculița FamilyRound</p>
				<h2>Bucuria voastră ajunge mai departe.</h2>
				<p class="fr-piggybank-intro">Din fiecare joc cumpărat, punem deoparte 5 lei pentru o cauză. Când jocul este cumpărat printr-un link de recomandare, mai adăugăm 5 lei.</p>
			</div>

			<div class="fr-piggybank-detail-block">
				<div class="fr-contrib">
					<div class="fr-contrib-row">
						<span class="fr-contrib-amount">5 lei</span>
						<span class="fr-contrib-label">din fiecare joc cumpărat</span>
					</div>
					<div class="fr-contrib-row">
						<span class="fr-contrib-amount">+5 lei</span>
						<span class="fr-contrib-label">pentru fiecare joc cumpărat prin link de recomandare</span>
					</div>
				</div>

				<p class="fr-contrib-total">10 lei în total pentru fiecare joc cumpărat prin recomandare.</p>
				<a class="fr-cta-ghost fr-cta-ghost--on-dark" href="<?php echo esc_url( home_url( '/da-timpul-mai-departe/' ) ); ?>">Cum funcționează recomandările →</a>
			</div>
		</div>

		<div class="fr-piggybank-panel">
			<div data-fr-island="piggybank-counter" data-fr-variant="compact"></div>
		</div>
	</div>
</section>

<section class="fr-mapsection">
	<div class="fr-container">
		<div
			data-fr-island="map-points"
			data-fr-variant="home"
			data-title="Împreună, în tot mai multe locuri."
			data-subtitle="Descoperă localitățile din care au fost cumpărate jocurile FamilyRound."
			data-href="<?php echo esc_url( home_url( '/harta-familyround/' ) ); ?>"
		></div>
	</div>
</section>

<?php
// Story photo — swap this one file (or the URL below) to replace the image.
$fr_story_image = $images_url . 'story-family.jpg';
?>
<section class="fr-ourstory">
	<div class="fr-container fr-container--wide fr-ourstory-grid fr-fade-up">
		<figure class="fr-ourstory-figure">
			<img src="<?php echo esc_url( $fr_story_image ); ?>" width="1200" height="800" alt="O familie construind un fort din pături în sufragerie, petrecând timp împreună seara">
			<figcaption>Imagine de atmosferă · Va fi înlocuită cu fotografia noastră</figcaption>
		</figure>
		<div class="fr-ourstory-textcol">
			<div class="fr-ourstory-head">
				<p class="fr-collection-label">Povestea noastră</p>
				<h2>O familie, ca a voastră.</h2>
			</div>
			<div class="fr-ourstory-body">
				<p>FamilyRound a pornit de la o dorință simplă: să facem mai mult loc timpului petrecut împreună.</p>
				<p>Creăm jocuri care vă dau un început — o idee, o întrebare, o provocare. Povestea o scrieți voi.</p>
				<a class="fr-cta-ghost" href="<?php echo esc_url( home_url( '/povestea-noastra/' ) ); ?>">Cunoaște-ne povestea →</a>
			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>
