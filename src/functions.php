<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

///////////////////////////////////////////////////////////////////////////
/**
* Adds svg element tags to wp_kses() escaping
*/
if (!function_exists('gf_blocks_for_discogs__kses_additions')) {

	add_filter( 'wp_kses_allowed_html', 'gf_blocks_for_discogs__kses_additions', 10, 2 );
	function gf_blocks_for_discogs__kses_additions( $tags, $context ) {

		if ( $context !== 'post' ) {
			return $tags;
		}

		$tags['svg'] = array(
			'class'           => true,
			'aria-hidden'     => true,
			'aria-labelledby' => true,
			'role'            => true,
			'xmlns'           => true,
			'width'           => true,
			'height'          => true,
			'viewbox'         => true,
			'fill'            => true,
		);

		$tags['path'] = array(
			'd'    => true,
			'fill' => true,
		);

		return $tags;
	}

}

///////////////////////////////////////////////////////////////////////////
/**
* Return either "white" or "black" based on which contrasts best
* against the given background color.
*
* @param string $bg_color 6-digit hex color, with or without leading "#", e.g. "#336699" or "336699".
* @return string "white" or "black"
*/
if (!function_exists('gf_blocks_for_discogs__get_contrast_color')) {

	function gf_blocks_for_discogs__get_contrast_color( $bg_color ) {
		// Remove leading "#" if present
		$hex = ltrim( $bg_color, '#' );

		// If not exactly 6 characters, default to black
		if ( strlen( $hex ) !== 6 ) {
			return 'black';
		}

		// Split into RGB components
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		// Compute brightness: (299*R + 587*G + 114*B) / 1000
		$brightness = ( ( 299 * $r ) + ( 587 * $g ) + ( 114 * $b ) ) / 1000;

		// If bright, use black text; otherwise use white text
		return ( $brightness > 128 ) ? esc_attr('#000000') : esc_attr('#ffffff');
	}

}