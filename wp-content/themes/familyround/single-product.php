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

<section class="fr-gallery fr-reveal" id="toate-cele-30">
	<div class="fr-container fr-gallery-head">
		<h2>Unele sunt simple.<br>Altele vă trimit într-o aventură.</h2>
	</div>
	<div class="fr-gallery-track">
		<?php foreach ( $fr_cards as $num => $title ) : ?>
			<figure class="fr-gallery-card">
				<img src="<?php echo esc_url( $images_url . 'card-' . str_pad( $num, 2, '0', STR_PAD_LEFT ) . '.jpg' ); ?>" alt="Cartonaș FamilyRound nr. <?php echo esc_attr( $num ); ?> — <?php echo esc_attr( $title ); ?>" loading="lazy">
			</figure>
		<?php endforeach; ?>
	</div>
</section>

<section class="fr-box fr-reveal">
	<div class="fr-container fr-box-grid">
		<div class="fr-box-image">
			<img src="<?php echo esc_url( $images_url . 'product-box.jpg' ); ?>" alt="Cutia FamilyRound cu cartonașele de provocări">
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
