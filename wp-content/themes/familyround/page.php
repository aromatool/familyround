<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$fr_cart     = function_exists( 'is_cart' ) && is_cart();
$fr_checkout = function_exists( 'is_checkout' ) && is_checkout();
$fr_received = $fr_checkout && function_exists( 'is_order_received_page' ) && is_order_received_page();
?>

<?php if ( $fr_cart || $fr_checkout ) : ?>
	<?php
	$fr_step = $fr_received ? 2 : 1;
	$fr_title = $fr_received ? 'Comanda a ajuns la noi.' : ( $fr_checkout ? 'Livrare și plată.' : 'Coșul vostru.' );
	$fr_intro = $fr_received
		? 'Mai jos găsești detaliile comenzii. Îți vom trimite pe email informațiile următoare despre plată și livrare.'
		: ( $fr_checkout ? 'Completează adresa, alege livrarea și verifică totalul final înainte de plată. Poți comanda fără cont.' : 'Verifică jocurile și cantitățile. Adresa, livrarea și totalul final apar la pasul următor.' );
	?>
	<main class="fr-purchase fr-purchase--<?php echo esc_attr( $fr_received ? 'received' : ( $fr_checkout ? 'checkout' : 'cart' ) ); ?>">
		<div class="fr-container">
			<nav class="fr-purchase-progress" aria-label="Pașii comenzii">
				<ol>
					<li<?php echo 1 === $fr_step ? ' aria-current="step"' : ' class="is-complete"'; ?>><span>01</span> Datele comenzii</li>
					<li<?php echo 2 === $fr_step ? ' aria-current="step"' : ''; ?>><span>02</span> Confirmare</li>
				</ol>
			</nav>
			<div class="fr-purchase-intro">
				<div>
					<h1><?php echo esc_html( $fr_title ); ?></h1>
					<p><?php echo esc_html( $fr_intro ); ?></p>
				</div>
				<aside class="fr-purchase-promise" aria-label="Contribuția FamilyRound">
					<strong>Un joc. Timp împreună. Bine dus mai departe.</strong>
					<p>Pentru fiecare joc eligibil cumpărat, punem noi 5 lei în Pușculiță după confirmarea comenzii.</p>
				</aside>
			</div>
			<div class="fr-purchase-content">
				<?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
			</div>
		</div>
	</main>
<?php else : ?>
	<main class="fr-container fr-generic-page">
		<?php while ( have_posts() ) : the_post(); ?>
			<h1><?php the_title(); ?></h1>
			<div><?php the_content(); ?></div>
		<?php endwhile; ?>
	</main>
<?php endif; ?>

<?php get_footer(); ?>
