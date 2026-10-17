<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$images_url = get_theme_file_uri( 'assets/images/' );
$shop_url = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/magazin/' );
$fr_games = function_exists( 'wc_get_products' ) ? wc_get_products( array( 'limit' => -1, 'orderby' => 'date', 'order' => 'ASC', 'status' => 'publish' ) ) : array();
$first_game_url = $shop_url;
$family_box_published = false;
foreach ( $fr_games as $fr_game ) {
	if ( false !== stripos( $fr_game->get_name(), '30 de provocări' ) ) {
		$first_game_url = get_permalink( $fr_game->get_id() );
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
    <p class="fr-home-overline">FamilyRound · experiențe pentru familii</p>
    <h1 id="fr-home-title">Din timpul împreună se nasc <em>poveștile voastre.</em></h1>
    <p>Jocuri care vă dau un motiv să ieșiți din rutină, să vă descoperiți altfel și să adunați amintiri numai ale voastre.</p>
   </div>
   <span class="fr-home-hero-aside" aria-hidden="true">Un cerc. Nenumărate feluri de a fi împreună.</span>
  </div>
  <nav class="fr-home-hero-chapters" aria-label="Descoperă universul FamilyRound" data-fr-hero-chapters>
   <a class="fr-home-hero-chapter" href="#experiente">
    <span class="fr-home-hero-chapter-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M7 10.5h18v15H7zM10.5 7h11M12 15.5h8M12 20.5h5"/></svg></span>
    <span class="fr-home-hero-chapter-copy"><span>Experiențe</span><strong>Jocuri care vă aduc mai aproape.</strong><small>Descoperă colecția</small></span>
    <span class="fr-home-hero-chapter-arrow" aria-hidden="true">↘</span>
   </a>
   <a class="fr-home-hero-chapter fr-home-hero-chapter--giving is-active" href="#pusculita">
    <span class="fr-home-hero-chapter-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M16 26s-9-5.2-9-12.1A5.9 5.9 0 0 1 17.1 9.8 5.9 5.9 0 0 1 27 13.9C27 20.8 16 26 16 26Z"/><path d="M13 14h6M16 11v6"/></svg></span>
    <span class="fr-home-hero-chapter-copy"><span>Pușculița</span><strong><b>5 lei</b> din fiecare joc merg mai departe.</strong><small>Prin recomandare pot deveni 10 lei</small></span>
    <span class="fr-home-hero-chapter-arrow" aria-hidden="true">↘</span>
   </a>
   <a class="fr-home-hero-chapter" href="#harta">
    <span class="fr-home-hero-chapter-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M16 27s8-7.4 8-15a8 8 0 1 0-16 0c0 7.6 8 15 8 15Z"/><circle cx="16" cy="12" r="2.75"/></svg></span>
    <span class="fr-home-hero-chapter-copy"><span>Harta</span><strong>Vezi cum crește cercul FamilyRound.</strong><small>Descoperă localitățile</small></span>
    <span class="fr-home-hero-chapter-arrow" aria-hidden="true">↘</span>
   </a>
  </nav>
 </section>

 <section class="fr-home-belief" id="ideea" aria-labelledby="fr-home-belief-title">
  <div class="fr-container fr-home-belief-grid">
   <div class="fr-home-belief-copy fr-fade-up"><p class="fr-home-overline">De ce existăm</p><h2 id="fr-home-belief-title">Zilele se umplu repede. <em>Clipele voastre merită loc.</em></h2><p>Între serviciu, școală și tot ce trebuie făcut, timpul împreună ajunge ușor pe lista de „cândva”. FamilyRound creează începuturi simple pentru momente în care să fiți cu adevărat acolo, unii pentru alții.</p><a class="fr-home-textlink" href="<?php echo esc_url( home_url( '/povestea-noastra/' ) ); ?>">Povestea FamilyRound <span aria-hidden="true">↗</span></a></div>
   <figure class="fr-home-belief-image fr-fade-up"><img src="<?php echo esc_url( $images_url . 'story-family.jpg' ); ?>" width="1200" height="800" alt="O mamă și copiii ei construiesc împreună un cort din pături, acasă" loading="lazy"><figcaption>Uneori, o pătură devine începutul unei aventuri.</figcaption></figure>
  </div>
 </section>

 <section class="fr-home-rhythm" aria-labelledby="fr-home-rhythm-title">
  <div class="fr-container fr-home-rhythm-heading fr-fade-up"><span class="fr-home-rhythm-numeral" aria-hidden="true">∞</span><div><h2 id="fr-home-rhythm-title">Nu există un singur fel de a fi <em>împreună.</em></h2><p>Unele zile cer o aventură. Alte seri, doar un loc la aceeași masă. Noi le facem loc pe amândouă.</p></div></div>
  <div class="fr-home-scenes" data-fr-scenes>
   <figure class="fr-home-scene fr-home-scene--outside"><img src="<?php echo esc_url( $images_url . 'home/picnic.webp' ); ?>" width="1122" height="1402" alt="Părinți și copii râd împreună la un picnic" loading="lazy"><figcaption><span>01 / Dincolo de rutina de azi</span><strong>O zi care se povestește din nou.</strong></figcaption></figure>
   <figure class="fr-home-scene fr-home-scene--inside"><img src="<?php echo esc_url( $images_url . 'together-try.jpg' ); ?>" width="1200" height="800" alt="O mamă și fiica ei modelează lut împreună acasă" loading="lazy"><figcaption><span>02 / Chiar aici, acasă</span><strong>Un moment numai al vostru.</strong></figcaption></figure>
  </div>
  <div class="fr-container fr-home-rhythm-foot"><span>Feluri diferite de a vă apropia.</span><span>Același cerc.</span></div>
 </section>

 <section class="fr-home-experiences" id="experiente" aria-labelledby="fr-home-exp-title"><div class="fr-container">
  <div class="fr-home-section-intro fr-fade-up"><p class="fr-home-overline">Colecția FamilyRound</p><h2 id="fr-home-exp-title">Experiențe care încep cu un joc.<br><em>Și continuă cu voi.</em></h2><p>Fiecare joc deschide o altă cale către timpul petrecut împreună. Alegeți-o pe cea care vi se potrivește acum.</p></div>
  <?php
  $display_games = ! empty( $fr_games ) ? $fr_games : array( null );
  foreach ( $display_games as $index => $game ) :
   $is_first = ! $game || false !== stripos( $game->get_name(), '30 de provocări' );
   $title = $game ? preg_replace( '/^\s*FamilyRound\s*[–—\-]\s*/u', '', $game->get_name() ) : '30 de provocări în familie';
   $photo = $is_first ? $images_url . 'unbox/lifestyle_masa.webp' : ( $game && $game->get_image_id() ? wp_get_attachment_image_url( $game->get_image_id(), 'large' ) : $images_url . 'product-box.jpg' );
   $desc = $is_first ? 'O cutie cu 30 de invitații la aventuri adevărate: un picnic, o călătorie cu trenul, o idee nebunească făcută împreună. Iar harta amintirilor păstrează pe perete ce a rămas din fiecare zi.' : trim( wp_strip_all_tags( $game->get_short_description() ) );
   $url = $game ? get_permalink( $game->get_id() ) : $shop_url;
  ?>
   <article class="fr-home-feature fr-fade-up"><div class="fr-home-feature-visual"><img src="<?php echo esc_url( $photo ); ?>" width="1448" height="1086" alt="<?php echo esc_attr( $is_first ? 'Concept vizual al cutiei 30 de provocări în familie, alături de cartonașe și harta amintirilor' : $title ); ?>" loading="lazy"></div><div class="fr-home-feature-copy"><p class="fr-home-feature-index"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?> · <?php echo $is_first ? 'Prima experiență' : 'Experiență FamilyRound'; ?></p><h3><?php echo esc_html( $title ); ?></h3><?php if ( $desc ) : ?><p><?php echo esc_html( $desc ); ?></p><?php endif; ?><a class="fr-home-button" href="<?php echo esc_url( $url ); ?>">Descoperă experiența <span aria-hidden="true">↗</span></a><?php if ( $is_first ) : ?><span class="fr-home-feature-note">30 de cartonașe · harta amintirilor · accesorii pentru amintiri</span><?php endif; ?></div></article>
  <?php endforeach; ?>
  <?php if ( ! $family_box_published ) : ?>
  <div class="fr-home-next fr-fade-up"><div class="fr-home-next-copy"><p class="fr-home-feature-index"><?php echo esc_html( sprintf( '%02d', count( $display_games ) + 1 ) ); ?> · În lucru</p><h3>Family Box</h3><p>Cinci seturi a câte 40 de cartonașe, gândite pentru joaca de acasă: trageți un cartonaș, intrați în joc și descoperiți ceva nou unii despre alții. O pregătim cu grijă.</p><span class="fr-home-next-status">Următoarea experiență se pregătește <span aria-hidden="true">↗</span></span></div><div class="fr-home-next-art" aria-hidden="true"><div class="fr-home-next-word"><span>Râs</span><span>Descoperă</span><span>Imaginează</span><span>Între noi</span><span>Mișcă</span></div></div></div>
  <?php endif; ?>
 </div></section>

 <section class="fr-home-keepsake" aria-labelledby="fr-home-keepsake-title"><div class="fr-home-keepsake-image"><img src="<?php echo esc_url( $images_url . 'unbox/harta_perete.webp' ); ?>" width="1122" height="1402" alt="Concept vizual al unei hărți a amintirilor cu fotografii, desene și mici suveniruri, agățată pe perete" loading="lazy"></div><div class="fr-home-keepsake-copy fr-fade-up"><p class="fr-home-overline">Ce rămâne după joacă</p><h2 id="fr-home-keepsake-title">Nu doar „îți amintești când?”.<br><em>Ci ceva de arătat.</em></h2><p>Prima noastră experiență vine cu o hartă a amintirilor. Lipiți o fotografie, un bilet, o frunză sau desenul copilului. În timp, peretele vostru spune povestea familiei mai bine decât ar putea-o face orice joc.</p><a class="fr-home-textlink" href="<?php echo esc_url( $first_game_url ); ?>">Vezi prima experiență <span aria-hidden="true">↗</span></a></div></section>

 <section class="fr-piggybank fr-home-giving" id="pusculita" aria-labelledby="fr-home-giving-title"><div class="fr-container fr-piggybank-grid"><div class="fr-piggybank-textcol fr-fade-up"><div class="fr-piggybank-intro-block"><p class="fr-home-overline">Pușculița FamilyRound</p><h2 id="fr-home-giving-title">Cercul poate crește <em>dincolo de familia voastră.</em></h2><p class="fr-piggybank-intro">Pentru fiecare joc FamilyRound cumpărat, punem noi deoparte 5 lei în Pușculiță. La prima comandă a fiecărei familii venite prin linkul vostru de recomandare, mai adăugăm 5 lei pentru fiecare joc eligibil. Sumele alocate vor sprijini cauze pe care le vom prezenta transparent.</p></div><div class="fr-piggybank-detail-block"><div class="fr-contrib"><div class="fr-contrib-row"><span class="fr-contrib-amount">5 lei</span><span class="fr-contrib-label">puși deoparte pentru fiecare joc</span></div><div class="fr-contrib-row"><span class="fr-contrib-amount">+5 lei</span><span class="fr-contrib-label">per joc din prima comandă recomandată</span></div></div><p class="fr-contrib-total">La prima comandă prin recomandare, contribuția poate ajunge la 10 lei per joc. Voi nu plătiți nimic în plus.</p><div class="fr-home-giving-share"><strong>Un link personal. O idee împărtășită.</strong><span>Trimite-l unei familii dragi sau pune-l într-o postare. La prima ei comandă, noi adăugăm încă 5 lei pentru fiecare joc eligibil.</span></div><a class="fr-cta-ghost fr-cta-ghost--on-dark" href="<?php echo esc_url( home_url( '/da-timpul-mai-departe/' ) ); ?>">Primește linkul tău →</a></div></div><div class="fr-piggybank-panel"><div data-fr-island="piggybank-counter" data-fr-variant="compact"></div></div></div></section>

 <section class="fr-home-map-intro" id="harta"><div class="fr-container fr-home-map-intro-grid fr-fade-up"><p class="fr-home-overline">Harta FamilyRound</p><div><h2>Din casa voastră, <em>în tot mai multe case.</em></h2><p>Pe hartă se aprind localitățile din care au fost cumpărate experiențele FamilyRound. Fiecare punct înseamnă un nou început undeva în țară.</p></div></div></section>
 <section class="fr-mapsection fr-home-map"><div class="fr-container"><div data-fr-island="map-points" data-fr-variant="home" data-title="Unde a ajuns FamilyRound" data-subtitle="Localitățile din care au fost cumpărate experiențele noastre." data-href="<?php echo esc_url( home_url( '/harta-familyround/' ) ); ?>"></div></div></section>

 <section class="fr-home-close" aria-labelledby="fr-home-close-title"><div class="fr-container fr-home-close-inner fr-fade-up"><span class="fr-home-close-mark" aria-hidden="true">◌</span><p class="fr-home-overline">Cercul FamilyRound</p><h2 id="fr-home-close-title">Unele povești merită <em>spuse mai departe.</em></h2><p>Începeți una împreună. Dacă vă place ce construim, invitați și o altă familie în cerc.</p><div class="fr-home-close-actions"><a class="fr-home-button fr-home-button--light" href="<?php echo esc_url( $first_game_url ); ?>">Descoperă prima experiență <span aria-hidden="true">↗</span></a><a class="fr-home-textlink fr-home-textlink--light" href="<?php echo esc_url( home_url( '/da-timpul-mai-departe/' ) ); ?>">Dă timpul mai departe <span aria-hidden="true">↗</span></a></div></div></section>
</main>
<?php get_footer(); ?>
