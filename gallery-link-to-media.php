<?php
/**
 * Plugin Name:       Gallery Link to Media
 * Plugin URI:        https://github.com/mikegoodstadt/gallery-link-to-media
 * Description:       Makes native WordPress Gallery block images link to their media files by default and provides tools to update existing galleries.
 * Version:           0.1.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author:            Mike Goodstadt
 * Author URI:        https://github.com/mikegoodstadt
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gallery-link-to-media
 *
 * @package GalleryLinkToMedia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GLTM_VERSION', '0.1.0' );
define( 'GLTM_PLUGIN_FILE', __FILE__ );
define( 'GLTM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GLTM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load the Gallery editor behavior.
 */
function gltm_enqueue_block_editor_assets() {
	$asset_path = GLTM_PLUGIN_DIR . 'assets/js/editor.js';

	wp_enqueue_script(
		'gltm-editor',
		GLTM_PLUGIN_URL . 'assets/js/editor.js',
		array(
			'wp-block-editor',
			'wp-blocks',
			'wp-compose',
			'wp-core-data',
			'wp-data',
			'wp-element',
			'wp-hooks',
		),
		file_exists( $asset_path ) ? (string) filemtime( $asset_path ) : GLTM_VERSION,
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'gltm_enqueue_block_editor_assets' );
