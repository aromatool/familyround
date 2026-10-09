<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<div class="fr-container" style="padding: 48px 24px;">
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<article>
			<h1><?php the_title(); ?></h1>
			<div><?php the_content(); ?></div>
		</article>
	<?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>
