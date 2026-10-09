<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<footer class="fr-site-footer">
	<div class="fr-container">
		<p>&copy; <?php echo esc_html( date( 'Y' ) ); ?> FamilyRound — Mai mult timp împreună.</p>
		<nav class="fr-footer-links">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'footer',
				'container'      => false,
				'fallback_cb'    => false,
			) );
			?>
		</nav>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
