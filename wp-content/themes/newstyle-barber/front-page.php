<?php
/**
 * Front page: Peluquería New Style.
 *
 * The content (hero, servicios, nosotros, espacio, galería, contacto) is
 * edited as blocks in Páginas → Inicio; this template only provides the
 * full-width wrapper. The theme's CSS classes (nsb-*) style the blocks.
 *
 * @package NewStyle_Barber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main" class="nsb site-main">
	<?php
	while ( have_posts() ) {
		the_post();
		the_content();
	}
	?>
</main>

<?php
get_footer();
