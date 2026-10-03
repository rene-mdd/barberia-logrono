<?php
/**
 * Business data for Peluquería New Style (source: info.txt) and asset helpers.
 *
 * @package NewStyle_Barber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the shop's business information (used in the menu, footer, SEO
 * tags and structured data).
 *
 * @return array
 */
function nsb_info() {
	return array(
		'name'        => 'Peluquería New Style',
		'since'       => '2003',
		'years'       => '25',
		'description' => 'Brindamos excelente trabajo con más de 25 años de experiencia',
		'address'     => 'Plaza 1 de Mayo nº10 bajo (pasaje)',
		'postcode'    => '26002',
		'city'        => 'Logroño',
		'phone'       => '632 895 661',
		'phone_href'  => 'tel:+34632895661',
		'maps_query'  => 'Plaza 1 de Mayo 10, 26002 Logroño',
	);
}

/**
 * URL of a file inside the theme's assets folder.
 *
 * @param string $path Path relative to assets/.
 * @return string
 */
function nsb_asset( $path ) {
	return get_stylesheet_directory_uri() . '/assets/' . $path;
}
