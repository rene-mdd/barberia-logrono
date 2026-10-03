<?php
/**
 * Peluquería New Style — Astra child theme.
 *
 * @package NewStyle_Barber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NSB_VERSION', '1.2.0' );

require_once get_stylesheet_directory() . '/inc/business-info.php';

/**
 * Cache-busting version for a theme file: its modification time, so browsers
 * pick up every edit.
 *
 * @param string $path Path relative to the theme folder.
 * @return string
 */
function nsb_asset_version( $path ) {
	$file = get_stylesheet_directory() . '/' . $path;
	return file_exists( $file ) ? (string) filemtime( $file ) : NSB_VERSION;
}

/**
 * Styles and scripts.
 */
function nsb_enqueue_assets() {
	wp_enqueue_style(
		'nsb-fonts',
		'https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,500;1,700&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'nsb-style', get_stylesheet_uri(), array( 'astra-theme-css', 'nsb-fonts' ), nsb_asset_version( 'style.css' ) );
	wp_enqueue_script(
		'nsb-header',
		get_stylesheet_directory_uri() . '/assets/js/header.js',
		array(),
		nsb_asset_version( 'assets/js/header.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	if ( is_front_page() ) {
		foreach ( array( 'gallery', 'carousel' ) as $script ) {
			wp_enqueue_script(
				'nsb-' . $script,
				get_stylesheet_directory_uri() . '/assets/js/' . $script . '.js',
				array(),
				nsb_asset_version( 'assets/js/' . $script . '.js' ),
				array(
					'in_footer' => true,
					'strategy'  => 'defer',
				)
			);
		}
		wp_add_inline_script( 'nsb-gallery', 'window.nsbI18n = ' . wp_json_encode( nsb_js_strings() ) . ';', 'before' );
	}
}
add_action( 'wp_enqueue_scripts', 'nsb_enqueue_assets', 20 );

/**
 * Whether the page is being shown in English (TranslatePress switches the
 * locale on /en/ URLs).
 *
 * @return bool
 */
function nsb_is_english() {
	return 0 === strpos( determine_locale(), 'en' );
}

/**
 * Labels used by the carousel and lightbox scripts, in the current language.
 *
 * @return array
 */
function nsb_js_strings() {
	if ( nsb_is_english() ) {
		return array(
			'carousel' => 'Photos of the shop',
			'scroll'   => 'Photos of the shop (scrollable)',
			'prev'     => 'Previous photo',
			'next'     => 'Next photo',
			'pause'    => 'Pause',
			'play'     => 'Play',
			'goTo'     => 'Go to photo %d',
			'dialog'   => 'Enlarged gallery photo',
			'close'    => 'Close',
			'prevImg'  => 'Previous',
			'nextImg'  => 'Next',
		);
	}
	return array(
		'carousel' => 'Fotos del local',
		'scroll'   => 'Fotos del local (desplazable)',
		'prev'     => 'Foto anterior',
		'next'     => 'Foto siguiente',
		'pause'    => 'Pausar',
		'play'     => 'Reproducir',
		'goTo'     => 'Ir a la foto %d',
		'dialog'   => 'Foto ampliada de la galería',
		'close'    => 'Cerrar',
		'prevImg'  => 'Anterior',
		'nextImg'  => 'Siguiente',
	);
}

/**
 * Block styles offered in the editor for the theme's buttons, and the theme
 * styles inside the editor so blocks look like the site.
 */
function nsb_editor_setup() {
	$styles = array(
		'nsb-gold'    => 'Dorado',
		'nsb-ghost'   => 'Contorno claro (fondo oscuro)',
		'nsb-outline' => 'Contorno oscuro (fondo claro)',
	);
	foreach ( $styles as $name => $label ) {
		register_block_style(
			'core/button',
			array(
				'name'  => $name,
				'label' => $label,
			)
		);
	}
	register_block_style(
		'core/gallery',
		array(
			'name'  => 'nsb-carousel',
			'label' => 'Carrusel',
		)
	);
	register_block_style(
		'core/gallery',
		array(
			'name'  => 'nsb-lightbox',
			'label' => 'Cuadrícula con ampliación',
		)
	);
}
add_action( 'init', 'nsb_editor_setup' );

/**
 * Full-width canvas without title or sidebar on the front page.
 */
function nsb_front_page_layout() {
	if ( ! is_front_page() ) {
		return;
	}
	add_filter(
		'astra_page_layout',
		function () {
			return 'no-sidebar';
		}
	);
	add_filter(
		'astra_get_content_layout',
		function () {
			return 'page-builder';
		}
	);
	add_filter( 'astra_the_title_enabled', '__return_false' );
}
add_action( 'wp', 'nsb_front_page_layout' );

/**
 * Footer copyright text.
 *
 * @return string
 */
function nsb_footer_copyright() {
	$info  = nsb_info();
	$links = '';
	$page  = nsb_accessibility_page_id();
	if ( $page && 'publish' === get_post_status( $page ) ) {
		$links = sprintf(
			'<br><span class="nsb-footer-links"><a href="%1$s">%2$s</a></span>',
			esc_url( get_permalink( $page ) ),
			esc_html( get_the_title( $page ) )
		);
	}
	return sprintf(
		'© %1$s %2$s · %3$s, %4$s %5$s · <a href="%6$s">%7$s</a>%8$s',
		gmdate( 'Y' ),
		esc_html( $info['name'] ),
		esc_html( $info['address'] ),
		esc_html( $info['postcode'] ),
		esc_html( $info['city'] ),
		esc_attr( $info['phone_href'] ),
		esc_html( $info['phone'] ),
		$links
	);
}
add_filter( 'astra_get_option_footer-copyright-editor', 'nsb_footer_copyright' );

/**
 * Meta description for the current page: the excerpt on inner pages, the
 * business summary on the front page.
 *
 * @return string
 */
function nsb_meta_description() {
	$info = nsb_info();
	if ( is_singular() && ! is_front_page() && has_excerpt() ) {
		$excerpt = wp_strip_all_tags( get_the_excerpt() );
		return nsb_is_english() && function_exists( 'trp_translate' ) ? trp_translate( $excerpt, 'en_US', false ) : $excerpt;
	}
	if ( nsb_is_english() ) {
		return sprintf(
			'%1$s, a barbershop in %2$s since %3$s: fades, designs, classic cuts, beards and kids\' cuts. Plaza 1 de Mayo nº10, %2$s. Tel. %4$s.',
			$info['name'],
			$info['city'],
			$info['since'],
			$info['phone']
		);
	}
	return sprintf(
		'%1$s, barbería en %2$s desde %3$s: degradados, diseños, corte clásico, barba y corte infantil. %4$s. Tel. %5$s.',
		$info['name'],
		$info['city'],
		$info['since'],
		$info['address'],
		$info['phone']
	);
}

/**
 * SEO tags in <head>: description, Open Graph / Twitter card, favicon and
 * LocalBusiness structured data (front page).
 */
function nsb_seo_head() {
	$info        = nsb_info();
	$description = nsb_meta_description();
	$title       = wp_get_document_title();
	$url         = is_front_page() ? home_url( '/' ) : get_permalink();
	$image       = nsb_asset( 'img/og-image.jpg' );
	?>
	<meta name="description" content="<?php echo esc_attr( $description ); ?>">
	<meta property="og:type" content="website">
	<meta property="og:locale" content="<?php echo nsb_is_english() ? 'en_GB' : 'es_ES'; ?>">
	<meta property="og:site_name" content="<?php echo esc_attr( $info['name'] ); ?>">
	<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
	<meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
	<meta property="og:url" content="<?php echo esc_url( $url ); ?>">
	<meta property="og:image" content="<?php echo esc_url( $image ); ?>">
	<meta property="og:image:width" content="1200">
	<meta property="og:image:height" content="630">
	<meta property="og:image:alt" content="<?php echo esc_attr( nsb_is_english() ? 'Peluquería New Style shop sign in Logroño' : 'Rótulo de Peluquería New Style en Logroño' ); ?>">
	<meta name="twitter:card" content="summary_large_image">
	<meta name="theme-color" content="#151515">
	<?php if ( ! has_site_icon() ) : ?>
		<link rel="icon" href="<?php echo esc_url( nsb_asset( 'img/favicon-32.png' ) ); ?>" sizes="32x32" type="image/png">
		<link rel="icon" href="<?php echo esc_url( nsb_asset( 'img/icon-512.png' ) ); ?>" sizes="512x512" type="image/png">
		<link rel="apple-touch-icon" href="<?php echo esc_url( nsb_asset( 'img/apple-touch-icon.png' ) ); ?>">
	<?php endif; ?>
	<?php
	if ( ! is_front_page() ) {
		return;
	}
	$schema = array(
		'@context'     => 'https://schema.org',
		'@type'        => 'HairSalon',
		'@id'          => home_url( '/#business' ),
		'name'         => $info['name'],
		'description'  => nsb_is_english() ? 'Excellent work backed by more than 25 years of experience.' : $info['description'] . '.',
		'url'          => home_url( '/' ),
		'telephone'    => '+34 ' . $info['phone'],
		'image'        => $image,
		'logo'         => nsb_asset( 'img/icon-512.png' ),
		'foundingDate' => $info['since'],
		'address'      => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $info['address'],
			'postalCode'      => $info['postcode'],
			'addressLocality' => $info['city'],
			'addressRegion'   => 'La Rioja',
			'addressCountry'  => 'ES',
		),
		'hasMap'       => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $info['maps_query'] ),
		'areaServed'   => $info['city'],
	);
	printf(
		"<script type=\"application/ld+json\">%s</script>\n",
		wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_head', 'nsb_seo_head', 2 );

/**
 * Browser tab title in English on /en/ pages (the site tagline and page
 * titles come from the Spanish settings).
 *
 * @param array $parts Title parts.
 * @return array
 */
function nsb_document_title_parts( $parts ) {
	if ( ! nsb_is_english() ) {
		return $parts;
	}
	if ( isset( $parts['tagline'] ) ) {
		$parts['tagline'] = 'Barbershop in Logroño since 2003';
	}
	if ( isset( $parts['title'] ) && function_exists( 'trp_translate' ) ) {
		$parts['title'] = trp_translate( $parts['title'], 'en_US', false );
	}
	return $parts;
}
add_filter( 'document_title_parts', 'nsb_document_title_parts' );

/**
 * Connect early to Google Fonts.
 *
 * @param array  $urls          URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 * @return array
 */
function nsb_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'nsb_resource_hints', 10, 2 );

/**
 * Keep user accounts (and the admin user name) out of the XML sitemap.
 *
 * @param WP_Sitemaps_Provider $provider Sitemap provider.
 * @param string               $name     Provider name.
 * @return WP_Sitemaps_Provider|false
 */
function nsb_sitemap_providers( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'nsb_sitemap_providers', 10, 2 );

/**
 * Adds the phone number as a highlighted last item of the main menu.
 *
 * @param string   $items Menu items HTML.
 * @param stdClass $args  wp_nav_menu() arguments.
 * @return string
 */
function nsb_menu_phone( $items, $args ) {
	if ( ! in_array( $args->theme_location, array( 'primary', 'mobile_menu' ), true ) ) {
		return $items;
	}
	$info  = nsb_info();
	$phone = sprintf(
		'<li class="menu-item nsb-menu-phone"><a class="menu-link" href="%1$s" aria-label="%3$s %2$s"><span class="nsb-menu-phone__icon" aria-hidden="true">✆</span>%2$s</a></li>',
		esc_attr( $info['phone_href'] ),
		esc_html( $info['phone'] ),
		esc_attr( nsb_is_english() ? 'Call' : 'Llamar al' )
	);

	// Keep the language switcher (TranslatePress) as the last item.
	$pos = strpos( $items, 'trp-language-switcher-container' );
	if ( false !== $pos ) {
		$li = strrpos( substr( $items, 0, $pos ), '<li' );
		return substr( $items, 0, $li ) . $phone . substr( $items, $li );
	}
	return $items . $phone;
}
add_filter( 'wp_nav_menu_items', 'nsb_menu_phone', 10, 2 );

/**
 * Let TranslatePress translate image alt text, so the English pages describe
 * the photos in English (WCAG 1.1.1 / 3.1.1).
 *
 * @param array $accessors Translatable node accessors.
 * @return array
 */
function nsb_translate_image_alt( $accessors ) {
	$accessors['image_alt'] = array(
		'selector'  => 'img[alt]',
		'accessor'  => 'alt',
		'attribute' => true,
	);
	return $accessors;
}
add_filter( 'trp_node_accessors', 'nsb_translate_image_alt' );

/**
 * Language switcher link: declare the language of its text and target
 * (WCAG 3.1.2) so "English" / "Español" are pronounced correctly.
 *
 * @param array    $atts Link attributes.
 * @param WP_Post  $item Menu item.
 * @return array
 */
function nsb_language_switcher_attributes( $atts, $item ) {
	if ( 'language_switcher' !== $item->object || empty( $atts['href'] ) ) {
		return $atts;
	}
	$lang             = false !== strpos( $atts['href'], '/en/' ) ? 'en' : 'es';
	$atts['lang']     = $lang;
	$atts['hreflang'] = $lang;
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'nsb_language_switcher_attributes', 20, 2 );

/**
 * Kit Digital funding notice (EU NextGenerationEU + Plan de Recuperación),
 * shown on every page just above the footer.
 */
function nsb_kit_digital_banner() {
	?>
	<aside class="nsb-funding" aria-label="Financiación europea">
		<div class="nsb-funding__inner">
			<img class="nsb-funding__eu" src="<?php echo esc_url( nsb_asset( 'img/financiado-ue-nextgenerationeu.png' ) ); ?>" width="1000" height="250" alt="Financiado por la Unión Europea - NextGenerationEU" lang="es" loading="lazy" decoding="async">
			<img class="nsb-funding__prtr" src="<?php echo esc_url( nsb_asset( 'img/plan-recuperacion-prtr.png' ) ); ?>" width="700" height="142" alt="Plan de Recuperación, Transformación y Resiliencia" lang="es" loading="lazy" decoding="async">
		</div>
	</aside>
	<?php
}
add_action( 'astra_footer_before', 'nsb_kit_digital_banner' );

/**
 * ID of the accessibility statement page (0 if it has not been created).
 *
 * @return int
 */
function nsb_accessibility_page_id() {
	return (int) get_option( 'nsb_accessibility_page', 0 );
}

/**
 * Creates the "Declaración de accesibilidad" page once. Editors can change
 * its text afterwards from Páginas.
 */
function nsb_create_accessibility_page() {
	if ( nsb_accessibility_page_id() && get_post( nsb_accessibility_page_id() ) ) {
		return;
	}
	$info    = nsb_info();
	$content = '
<!-- wp:paragraph -->
<p>' . esc_html( $info['name'] ) . ' se ha comprometido a hacer accesible su sitio web, de conformidad con las Pautas de Accesibilidad para el Contenido Web (WCAG) 2.1, nivel AA, y la norma UNE-EN 301 549.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Situación de cumplimiento</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Este sitio web es <strong>parcialmente conforme</strong> con las WCAG 2.1 nivel AA debido al contenido de terceros que se indica a continuación.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Contenido no accesible</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><li>El mapa de Google Maps de la sección de contacto es un servicio de terceros cuya accesibilidad no controlamos. La misma información (dirección y enlace «Cómo llegar») se ofrece en texto junto al mapa.</li></ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Preparación de esta declaración</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Esta declaración se preparó el ' . esc_html( date_i18n( 'j \d\e F \d\e Y' ) ) . ' mediante una autoevaluación del sitio con herramientas automáticas y revisión manual (navegación con teclado, contraste de colores, textos alternativos, idioma de la página y estructura de encabezados).</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Observaciones y contacto</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Si encuentra alguna barrera de accesibilidad en este sitio web o necesita la información en otro formato, puede comunicárnoslo llamando al <a href="' . esc_attr( $info['phone_href'] ) . '">' . esc_html( $info['phone'] ) . '</a> o en nuestro local: ' . esc_html( $info['address'] . ', ' . $info['postcode'] . ' ' . $info['city'] ) . '.</p>
<!-- /wp:paragraph -->
';
	$page_id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Declaración de accesibilidad',
			'post_name'    => 'declaracion-de-accesibilidad',
			'post_excerpt' => 'Declaración de accesibilidad del sitio web de ' . $info['name'] . ' según las WCAG 2.1 nivel AA.',
			'post_content' => $content,
		)
	);
	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_option( 'nsb_accessibility_page', $page_id );
	}
}

/**
 * One-time setup on activation: text logo, site name and a Spanish anchor menu.
 */
function nsb_setup_on_activation() {
	$info = nsb_info();

	// The stock Astra logo is replaced by the site title as a text logo.
	set_theme_mod( 'custom_logo', '' );
	$astra_settings = get_option( ASTRA_THEME_SETTINGS, array() );
	$astra_settings['display-site-title-responsive'] = array(
		'desktop' => 1,
		'tablet'  => 1,
		'mobile'  => 1,
	);
	update_option( ASTRA_THEME_SETTINGS, $astra_settings );

	nsb_create_accessibility_page();

	update_option( 'blogname', $info['name'] );
	update_option( 'blogdescription', 'Barbería en ' . $info['city'] . ' desde ' . $info['since'] );

	$menu_name = 'Barbería';
	$menu      = wp_get_nav_menu_object( $menu_name );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $menu_name );
		if ( is_wp_error( $menu_id ) ) {
			return;
		}
		$items = array(
			'Inicio'    => '#inicio',
			'Servicios' => '#services',
			'Nosotros'  => '#about',
			'Espacio'   => '#espacio',
			'Galería'   => '#gallery',
			'Contacto'  => '#contact',
		);
		foreach ( $items as $title => $anchor ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'  => $title,
					'menu-item-url'    => home_url( '/' ) . $anchor,
					'menu-item-type'   => 'custom',
					'menu-item-status' => 'publish',
				)
			);
		}
	} else {
		$menu_id = $menu->term_id;
	}

	$locations                = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary']     = $menu_id;
	$locations['mobile_menu'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}
add_action( 'after_switch_theme', 'nsb_setup_on_activation' );

/**
 * Disable WordPress's sizes="auto" on lazy images: combined with the theme's
 * fixed-ratio photo boxes (gallery, carousel, cards) Chrome never loads them.
 * srcset still lets the browser pick a suitable size.
 */
add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );
