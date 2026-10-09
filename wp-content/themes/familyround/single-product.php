<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$images_url = get_theme_file_uri( 'assets/images/' );
$fr_cards   = fr_challenge_cards();
?>

<div class="fr-container fr-woocommerce">
	<?php
	while ( have_posts() ) :
		the_post();
		wc_get_template_part( 'content', 'single-product' );
	endwhile;
	?>
</div>

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

<section class="fr-gallery fr-reveal" id="toate-cele-30">
	<div class="fr-container fr-gallery-head">
		<h2>Unele sunt simple.<br>Altele vă trimit într-o aventură.</h2>
	</div>
	<div class="fr-gallery-track">
		<?php foreach ( $fr_cards as $num => $title ) : ?>
			<figure class="fr-gallery-card">
				<img src="<?php echo esc_url( $images_url . 'card-' . str_pad( $num, 2, '0', STR_PAD_LEFT ) . '.jpg' ); ?>" width="408" height="520" alt="Cartonaș FamilyRound nr. <?php echo esc_attr( $num ); ?> — <?php echo esc_attr( $title ); ?>" loading="lazy">
			</figure>
		<?php endforeach; ?>
	</div>
</section>

<section class="fr-box fr-reveal">
	<div class="fr-container fr-box-grid">
		<div class="fr-box-image">
			<img src="<?php echo esc_url( $images_url . 'product-box.jpg' ); ?>" width="1000" height="666" alt="Cutia FamilyRound cu cartonașele de provocări">
		</div>
		<div class="fr-box-text">
			<p class="fr-box-cap">Ce găsești în cutie</p>
			<h2>Un set complet, gândit pentru 30 de momente speciale</h2>
			<ul class="fr-box-list">
				<li><strong>30 de cartonașe cu provocări</strong> — fiecare propune o experiență diferită, cu instrucțiuni simple și idei de la care să porniți.</li>
				<li><strong>Posterul „Amintiri care rămân”</strong> — locul unde adunați, pe parcurs, fotografii, bilețele și urmele celor 30 de experiențe.</li>
				<li><strong>Instrucțiuni de început</strong> — tot ce trebuie să știți, fără pregătiri complicate și fără reguli de memorat.</li>
			</ul>
		</div>
	</div>
</section>

<?php get_footer(); ?>
