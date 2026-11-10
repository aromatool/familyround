<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main class="fr-referral-page">
 <?php if ( isset( $_GET['fr_updates_done'] ) ) : ?>
 <section class="fr-container fr-referral-unsubscribe"><h1>Te-ai dezabonat.</h1><p>Nu îți vom mai trimite actualizări periodice despre recomandări. Codul și pagina de impact rămân disponibile.</p><a href="<?php echo esc_url( home_url( '/da-timpul-mai-departe/' ) ); ?>">Înapoi la recomandări →</a></section>
 <?php elseif ( isset( $_GET['fr_updates'] ) ) : ?>
 <section class="fr-container fr-referral-unsubscribe"><h1>Oprim emailurile de progres?</h1>
 <?php if ( FR_Referral_Digest::valid_unsubscribe_request() ) : ?>
 <p>Codul tău va continua să funcționeze. Doar actualizările periodice se opresc.</p>
 <form method="post"><input type="hidden" name="fr_updates_unsubscribe" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['fr_updates'] ) ) ); ?>"><?php wp_nonce_field( 'fr_updates_unsubscribe', 'fr_updates_nonce' ); ?><button type="submit" class="fr-referral-primary">Da, dezabonează-mă</button></form>
 <?php else : ?><p>Linkul de dezabonare nu este valid.</p><?php endif; ?>
 </section>
 <?php else : ?>
 <?php if ( isset( $_GET['code'] ) || isset( $_GET['token'] ) ) : ?>
 <section class="fr-referral-impact-hero" aria-labelledby="fr-referral-title">
  <div class="fr-container fr-referral-impact-intro">
   <p class="fr-home-overline">Dă timpul mai departe</p>
   <h1 id="fr-referral-title">Impactul tău <em>FamilyRound.</em></h1>
  </div>
  <div class="fr-container fr-referral-panel fr-referral-panel--wide">
   <div data-fr-island="referral-program"></div>
  </div>
 </section>
 <?php else : ?>
 <section class="fr-referral-hero" aria-labelledby="fr-referral-title">
  <div class="fr-container fr-referral-hero-grid">
   <div class="fr-referral-hero-copy">
    <p class="fr-home-overline">Dă timpul mai departe</p>
    <h1 id="fr-referral-title">O idee bună trece <em>din familie în familie.</em></h1>
    <p>Crezi că unei alte familii i-ar plăcea ideea FamilyRound? Trimite mai departe un link personal. La prima comandă a fiecărei familii care vine prin el, noi punem încă 5 lei pentru fiecare joc în Pușculiță, peste cei 5 lei alocați oricum. Linkul tău poate fi folosit de oricâte familii noi.</p>
    <div class="fr-referral-formula"><span>5 lei <small>per joc</small></span><b>+</b><span>5 lei <small>per joc din prima comandă</small></span><b>=</b><span>10 lei <small>per joc, la prima recomandare</small></span></div>
   </div>
   <div class="fr-referral-panel">
    <p class="fr-referral-panel-heading">Linkul tău, pregătit de trimis</p>
    <div data-fr-island="referral-program"></div>
    <button type="button" class="fr-referral-see-impact" data-fr-impact-open>Ai deja un link? Vezi impactul tău →</button>
   </div>
  </div>
 </section>
 <?php endif; ?>
 <section class="fr-referral-how" aria-labelledby="fr-referral-how-title">
  <div class="fr-container">
   <div class="fr-referral-how-intro"><p class="fr-home-overline">Simplu, dintr-un gest</p><h2 id="fr-referral-how-title">Spui mai departe. <em>Cercul crește.</em></h2></div>
   <div class="fr-referral-how-grid">
    <div><span>01</span><h3>Primești linkul tău</h3><p>Completezi prenumele și emailul. Îți afișăm imediat linkul și îl trimitem și pe email.</p></div>
    <div><span>02</span><h3>Îl trimiți cum vrei</h3><p>Într-un mesaj, pe WhatsApp sau într-o postare. Povestește ce ți-a plăcut, cu cuvintele tale.</p></div>
    <div><span>03</span><h3>Noi adăugăm 5 lei per joc</h3><p>Bonusul se adaugă la prima comandă a cumpărătorului. La comenzile următoare rămân cei 5 lei obișnuiți per joc. Verificăm prima comandă după email sau telefon. Nu e un comision și nimeni nu plătește în plus.</p></div>
   </div>
  </div>
 </section>
 <?php endif; ?>
</main>
<?php get_footer(); ?>
