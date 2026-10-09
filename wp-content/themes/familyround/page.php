<?php if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<div class="fr-container" style="padding: 48px 24px; max-width: 760px;">
	<?php while ( have_posts() ) : the_post(); ?>
		<h1><?php the_title(); ?></h1>
		<div><?php the_content(); ?></div>
	<?php endwhile; ?>
</div>

<?php get_footer(); ?>
