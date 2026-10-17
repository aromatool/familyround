<?php if ( ! defined( 'ABSPATH' ) ) exit;

$fr_jocuri_url = home_url( '/#experiente' );

// Show social channels only once their real FamilyRound URLs are configured.
$fr_social = array_filter( array(
	'Instagram' => get_theme_mod( 'fr_instagram_url', '' ),
	'Facebook'  => get_theme_mod( 'fr_facebook_url', '' ),
) );

// "Preferințe cookie" is shown only if a real consent manager is wired to this hook.
$fr_has_cookie_prefs = has_action( 'fr_open_cookie_preferences' ) || function_exists( 'fr_cookie_prefs_url' );
?>
<footer class="fr-site-footer">
	<?php if ( ! is_front_page() ) : ?>
	<div class="fr-container fr-footer-cta">
		<h2>Următoarea amintire începe cu voi.</h2>
		<a class="fr-btn-gold" href="<?php echo esc_url( $fr_jocuri_url ); ?>">Descoperă experiențele</a>
	</div>
	<?php endif; ?>

	<div class="fr-container fr-footer-grid">
		<div class="fr-footer-brand">
			<a class="fr-footer-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="FamilyRound — acasă">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/logo-mark.svg' ) ); ?>" alt="" width="36" height="34">
				<span>FamilyRound</span>
			</a>
			<p class="fr-footer-tagline">Experiențe FamilyRound pentru timpul petrecut împreună.</p>
		</div>

		<nav class="fr-footer-col" aria-label="Descoperă">
			<p class="fr-footer-heading">Descoperă</p>
			<ul>
				<li><a href="<?php echo esc_url( $fr_jocuri_url ); ?>">Experiențele</a></li>
				<li><a href="<?php echo esc_url( home_url( '/povestea-noastra/' ) ); ?>">Povestea noastră</a></li>
				<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
			</ul>
		</nav>

		<nav class="fr-footer-col" aria-label="Împreună">
			<p class="fr-footer-heading">Împreună</p>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/pusculita-familyround/' ) ); ?>">Pușculița</a></li>
				<li><a href="<?php echo esc_url( home_url( '/harta-familyround/' ) ); ?>">Harta FamilyRound</a></li>
				<li><a href="<?php echo esc_url( home_url( '/da-timpul-mai-departe/' ) ); ?>">Dă timpul mai departe</a></li>
			</ul>
		</nav>

		<nav class="fr-footer-col" aria-label="Informații utile">
			<p class="fr-footer-heading">Informații utile</p>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/politica-de-retur/' ) ); ?>">Livrare și retur</a></li>
				<li><a href="<?php echo esc_url( home_url( '/termeni-si-conditii/' ) ); ?>">Termeni și condiții</a></li>
				<li><a href="<?php echo esc_url( home_url( '/politica-de-confidentialitate/' ) ); ?>">Confidențialitate</a></li>
				<?php if ( $fr_has_cookie_prefs ) : ?>
					<li><a href="<?php echo esc_url( function_exists( 'fr_cookie_prefs_url' ) ? fr_cookie_prefs_url() : '#' ); ?>" data-fr-cookie-prefs>Preferințe cookie</a></li>
				<?php endif; ?>
			</ul>
		</nav>
	</div>

	<div class="fr-footer-bottom">
		<div class="fr-container fr-container--wide fr-footer-bottom-inner">
			<p class="fr-footer-copy">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> FamilyRound</p>
			<?php if ( ! empty( $fr_social ) ) : ?>
				<ul class="fr-footer-social">
					<?php foreach ( $fr_social as $label => $url ) : ?>
						<li><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</footer>

<button class="fr-impact-trigger" type="button" data-fr-impact-open aria-haspopup="dialog" aria-controls="fr-impact-dialog">
	<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20.5s-8-4.7-8-10.4a4.5 4.5 0 0 1 8-2.8 4.5 4.5 0 0 1 8 2.8c0 5.7-8 10.4-8 10.4Z"/></svg>
	<span>Vezi impactul</span>
</button>
<dialog class="fr-impact-dialog" id="fr-impact-dialog" aria-labelledby="fr-impact-dialog-title" aria-describedby="fr-impact-dialog-description">
	<button class="fr-impact-close" type="button" data-fr-impact-close aria-label="Închide panoul"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M5 5l14 14M19 5 5 19"/></svg></button>
	<p class="fr-impact-brand">FamilyRound · Pușculița</p>
	<h2 id="fr-impact-dialog-title">Cât bine a dus mai departe codul tău?</h2>
	<p id="fr-impact-dialog-description">Introdu codul de recomandare. Vei vedea câte recomandări s-au confirmat și câți lei am pus deoparte prin ele.</p>
	<form method="get" action="<?php echo esc_url( home_url( '/da-timpul-mai-departe/' ) ); ?>">
		<label for="fr-impact-code">Cod de recomandare</label>
		<input id="fr-impact-code" name="code" type="text" maxlength="20" autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="De exemplu: 25C243" required>
		<button type="submit">Vezi recomandările <span aria-hidden="true">→</span></button>
	</form>
	<p class="fr-impact-dialog-note">Nu găsești codul? Este în emailul primit de la FamilyRound. <a href="<?php echo esc_url( home_url( '/da-timpul-mai-departe/' ) ); ?>">Obține un link personal</a>.</p>
</dialog>

<?php wp_footer(); ?>
</body>
</html>
