<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$images_url = get_theme_file_uri( 'assets/images/' );
$shop_url = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/magazin/' );
$fr_games = function_exists( 'wc_get_products' ) ? wc_get_products( array( 'limit' => -1, 'orderby' => 'date', 'order' => 'ASC', 'status' => 'publish' ) ) : array();
$first_game_url = $shop_url;
$first_game_price = '';
$first_game_cart_url = '';
$family_box_published = false;
foreach ( $fr_games as $fr_game ) {
	if ( false !== stripos( $fr_game->get_name(), '30 de provocări' ) ) {
		$first_game_url = get_permalink( $fr_game->get_id() );
		$first_game_price = $fr_game->get_price();
		$first_game_cart_url = fr_is_prelaunch() ? $first_game_url . '#fr-product-order' : $fr_game->add_to_cart_url();
	}
	if ( false !== stripos( $fr_game->get_name(), 'Family Box' ) ) {
		$family_box_published = true;
	}
}
?>
<main id="content" class="fr-home">
 <section class="fr-home-hero" aria-labelledby="fr-home-title">
  <div class="fr-home-hero-image"><img src="<?php echo esc_url( $images_url . 'hero-family-moment.jpg' ); ?>" width="1672" height="941" alt="O familie de mai multe generații stă împreună, râde și povestește" fetchpriority="high"></div>
  <div class="fr-container fr-home-hero-inner">
   <div class="fr-home-hero-copy">
    <p class="fr-home-overline">FamilyRound · experiențe pentru familii cu copii</p>
    <h1 id="fr-home-title">Faceți loc momentelor <em>care rămân cu voi.</em></h1>
    <p>Experiențe care vă dau un motiv să lăsați rutina deoparte, să vă descoperiți altfel și să adunați amintiri numai ale voastre.</p>
   </div>
   <span class="fr-home-hero-aside" aria-hidden="true">O provocare.<br>O zi de povestit.</span>
  </div>
  <nav class="fr-home-hero-chapters" aria-label="Descoperă universul FamilyRound" data-fr-hero-chapters>
   <a class="fr-home-hero-chapter fr-home-hero-chapter--product is-active" href="#experiente">
    <span class="fr-home-hero-chapter-icon fr-home-hero-chapter-icon--photo" aria-hidden="true"><img src="<?php echo esc_url( $images_url . 'unbox/lifestyle_masa.webp' ); ?>" alt="" width="64" height="64" loading="lazy"></span>
    <span class="fr-home-hero-chapter-copy"><span>Disponibil acum</span><strong>30 de provocări în familie</strong><small><?php echo esc_html( number_format( (float) $first_game_price, 0, ',', '.' ) ); ?> lei · Descoperă jocul</small></span>
    <span class="fr-home-hero-chapter-arrow" aria-hidden="true">↓</span>
   </a>
   <a class="fr-home-hero-chapter fr-home-hero-chapter--giving" href="#pusculita">
    <span class="fr-home-hero-chapter-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M16 26s-9-5.2-9-12.1A5.9 5.9 0 0 1 17.1 9.8 5.9 5.9 0 0 1 27 13.9C27 20.8 16 26 16 26Z"/><path d="M13 14h6M16 11v6"/></svg></span>
    <span class="fr-home-hero-chapter-copy"><span>Pușculița</span><strong><b>5 lei</b> din fiecare joc merg mai departe.</strong><small>Prin recomandare pot deveni 10 lei</small></span>
    <span class="fr-home-hero-chapter-arrow" aria-hidden="true">↓</span>
   </a>
   <a class="fr-home-hero-chapter" href="#harta">
    <span class="fr-home-hero-chapter-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M16 27s8-7.4 8-15a8 8 0 1 0-16 0c0 7.6 8 15 8 15Z"/><circle cx="16" cy="12" r="2.75"/></svg></span>
    <span class="fr-home-hero-chapter-copy"><span>Harta</span><strong>Vezi cum crește cercul FamilyRound.</strong><small>Descoperă localitățile</small></span>
    <span class="fr-home-hero-chapter-arrow" aria-hidden="true">↓</span>
   </a>
  </nav>
 </section>

 <section class="fr-home-product" id="experiente">
  <div class="fr-container fr-home-product-group">
   <p class="fr-home-product-group-label fr-fade-up">Experiențele FamilyRound</p>
   <div class="fr-home-product-inner fr-home-product-inner--featured fr-fade-up">
    <div class="fr-home-product-image"><img src="<?php echo esc_url( $images_url . 'product/complete_product.webp' ); ?>" width="1447" height="1087" alt="Cutia FamilyRound, cartonașele cu provocări, posterul pentru amintiri desfăcut și accesoriile incluse" loading="lazy"></div>
    <div class="fr-home-product-copy">
     <p class="fr-home-overline">Disponibil acum</p>
     <h2 id="fr-home-product-title">30 de provocări<br><em>în familie.</em></h2>
     <p>Alegeți un cartonaș, faceți ceva împreună și păstrați momentul. 30 de invitații la aventuri adevărate.</p>
     <?php if ( $first_game_cart_url ) : ?>
     <div class="fr-home-product-action">
      <a class="fr-hero-buy" href="<?php echo esc_url( $first_game_cart_url ); ?>">Comandă acum · <?php echo esc_html( number_format( (float) $first_game_price, 0, ',', '.' ) ); ?> lei <span aria-hidden="true">→</span></a>
      <a class="fr-home-product-details" href="<?php echo esc_url( $first_game_url ); ?>">Descoperă jocul <span aria-hidden="true">↗</span></a>
     </div>
     <?php endif; ?>
    </div>
   </div>

   <?php if ( ! $family_box_published ) : ?>
   <div class="fr-home-product-inner fr-home-product-inner--soon fr-fade-up">
    <div class="fr-home-product-image">
     <span class="fr-home-product-concept-tag">Concept</span>
     <img src="<?php echo esc_url( $images_url . 'unbox/family-box-concept.webp' ); ?>" width="1326" height="1200" alt="Concept vizual pentru Family Box — cărți tematice, jurnalul familiei și un medalion de lemn" loading="lazy">
    </div>
    <div class="fr-home-product-copy">
     <p class="fr-home-overline">În pregătire</p>
     <h2>Family Box</h2>
     <p>Următoarea experiență FamilyRound: cărți tematice pentru conversații mai adânci, un jurnal al familiei și un medalion de amintire. Lucrăm la ea acum.</p>
    </div>
   </div>
   <?php endif; ?>
  </div>
 </section>

 <section class="fr-home-belief" id="ideea" aria-labelledby="fr-home-belief-title">
  <div class="fr-container fr-home-belief-grid">
   <div class="fr-home-belief-copy fr-fade-up"><p class="fr-home-overline">De ce existăm</p><h2 id="fr-home-belief-title">Zilele se umplu repede. <em>Clipele voastre merită loc.</em></h2><p>Între serviciu, școală și tot ce trebuie făcut, timpul împreună ajunge ușor pe lista de „cândva”. FamilyRound creează începuturi simple pentru momente în care să fiți cu adevărat acolo, unii pentru alții.</p><a class="fr-home-textlink" href="<?php echo esc_url( home_url( '/povestea-noastra/' ) ); ?>">Povestea FamilyRound <span aria-hidden="true">↗</span></a></div>
   <figure class="fr-home-belief-image fr-fade-up"><img src="<?php echo esc_url( $images_url . 'story-family.jpg' ); ?>" width="1200" height="800" alt="Familia Maja privind împreună apusul, așezați pe stânci lângă mare" loading="lazy"><figcaption>Uneori, cea mai bună amintire e doar să fii acolo, împreună.</figcaption></figure>
  </div>
 </section>

 <section class="fr-home-rhythm" aria-labelledby="fr-home-rhythm-title">
  <div class="fr-container fr-home-rhythm-heading fr-fade-up"><span class="fr-home-rhythm-numeral" aria-hidden="true">∞</span><div><h2 id="fr-home-rhythm-title">Nu există un singur fel de a fi <em>împreună.</em></h2><p>Un picnic la marginea pădurii, o călătorie cu trenul sau orice altă ocazie din calendarul vostru. Noi vă dăm un motiv să ieșiți din rutină.</p></div></div>
  <div class="fr-home-scenes" data-fr-scenes>
   <figure class="fr-home-scene fr-home-scene--outside"><img src="<?php echo esc_url( $images_url . 'home/picnic.webp' ); ?>" width="1122" height="1402" alt="Părinți și copii râd împreună la un picnic" loading="lazy"><figcaption><span>01 / Dincolo de rutina de azi</span><strong>O zi care se povestește din nou.</strong></figcaption></figure>
   <figure class="fr-home-scene fr-home-scene--inside"><img src="<?php echo esc_url( $images_url . 'home/tren.webp' ); ?>" width="1200" height="800" alt="O familie într-o călătorie cu trenul, privind peisajul pe geam" loading="lazy"><figcaption><span>02 / Oriunde vă poartă drumul</span><strong>O aventură care începe azi.</strong></figcaption></figure>
  </div>
 </section>

 <section class="fr-home-keepsake" aria-labelledby="fr-home-keepsake-title"><div class="fr-home-keepsake-image"><img src="<?php echo esc_url( $images_url . 'unbox/harta_perete.webp' ); ?>" width="1122" height="1402" alt="Concept vizual al unei hărți a amintirilor cu fotografii, desene și mici suveniruri, agățată pe perete" loading="lazy"></div><div class="fr-home-keepsake-copy fr-fade-up"><p class="fr-home-overline">Ce rămâne după joacă</p><h2 id="fr-home-keepsake-title">Nu doar „îți amintești când?”.<br><em>Ci ceva de arătat.</em></h2><p>Prima noastră experiență vine cu o hartă a amintirilor. Lipiți o fotografie, un bilet, o frunză sau desenul copilului. În timp, peretele vostru spune povestea familiei mai bine decât ar putea-o face orice joc.</p><a class="fr-home-textlink" href="<?php echo esc_url( $first_game_url ); ?>">Vezi prima experiență <span aria-hidden="true">↗</span></a></div></section>

 <section class="fr-piggybank fr-home-giving" id="pusculita" aria-labelledby="fr-home-giving-title"><div class="fr-container fr-piggybank-grid"><div class="fr-piggybank-textcol fr-fade-up"><div class="fr-piggybank-intro-block"><p class="fr-home-overline">Pușculița FamilyRound</p><h2 id="fr-home-giving-title">Din fiecare joc, <em>5 lei merg către o cauză bună.</em></h2><p class="fr-piggybank-intro">Când alegeți o experiență FamilyRound, noi punem 5 lei în Pușculiță. Dacă o altă familie cumpără pentru prima dată prin linkul vostru, mai adăugăm încă 5 lei pentru fiecare joc din comanda ei — fără niciun cost în plus pentru voi.</p></div><div class="fr-piggybank-detail-block"><div class="fr-contrib"><div class="fr-contrib-row"><span class="fr-contrib-amount">5 lei</span><span class="fr-contrib-label">puși deoparte pentru fiecare joc</span></div><div class="fr-contrib-row"><span class="fr-contrib-amount">+5 lei</span><span class="fr-contrib-label">per joc din prima comandă recomandată</span></div></div><p class="fr-contrib-total">La prima comandă prin recomandare, contribuția poate ajunge la 10 lei per joc. Voi nu plătiți nimic în plus.</p><div class="fr-home-giving-share"><strong>Un link personal. O idee împărtășită.</strong><span>Trimite-l unei familii dragi sau pune-l într-o postare. La prima ei comandă, noi adăugăm încă 5 lei pentru fiecare joc eligibil.</span></div><a class="fr-cta-ghost fr-cta-ghost--on-dark" href="<?php echo esc_url( home_url( '/da-timpul-mai-departe/' ) ); ?>">Primește linkul tău →</a></div></div><div class="fr-piggybank-panel"><div data-fr-island="piggybank-counter" data-fr-variant="compact"></div></div></div></section>

 <section class="fr-mapsection fr-home-map" id="harta"><div class="fr-container"><div data-fr-island="map-points" data-fr-variant="home" data-title="Vezi cât de departe poate ajunge o recomandare." data-subtitle="Fiecare punct arată o localitate în care a ajuns o experiență FamilyRound. Explorează întreaga comunitate sau introdu codul tău pentru a vedea familiile care au intrat în cerc prin recomandarea ta." data-href="<?php echo esc_url( home_url( '/harta-familyround/' ) ); ?>"></div></div></section>

 <section class="fr-home-close fr-home-close--compact" aria-labelledby="fr-home-close-title">
  <div class="fr-home-close-image"><img class="fr-parallax-img" src="<?php echo esc_url( $images_url . 'together-try.jpg' ); ?>" width="1100" height="825" alt="" loading="lazy"></div>
  <div class="fr-container fr-home-close-inner fr-fade-up"><p class="fr-home-overline">Prima experiență FamilyRound</p><h2 id="fr-home-close-title">Următoarea amintire poate <em>începe chiar azi.</em></h2><p>30 de provocări care vă dau un motiv să ieșiți din rutină și să petreceți timp împreună.</p><div class="fr-home-close-actions"><a class="fr-home-button fr-home-button--light" href="<?php echo esc_url( $first_game_cart_url ?: $first_game_url ); ?>">Comandă acum · <?php echo esc_html( number_format( (float) $first_game_price, 0, ',', '.' ) ); ?> lei <span aria-hidden="true">→</span></a></div></div>
 </section>
</main>
<?php get_footer(); ?>
