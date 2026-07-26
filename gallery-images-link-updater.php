<?php
/**
 * Plugin Name:       Gallery Images Link Updater
 * Plugin URI:        https://github.com/mikegoodstadt/gallery-images-link-updater
 * Description:       Sets and updates link destinations for native WordPress Gallery blocks and their images.
 * Version:           0.2.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author:            Mike Goodstadt
 * Author URI:        https://github.com/mikegoodstadt
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gallery-images-link-updater
 *
 * @package GalleryLinkToMedia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GLTM_VERSION', '0.2.0' );
define( 'GLTM_PLUGIN_FILE', __FILE__ );
define( 'GLTM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GLTM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once GLTM_PLUGIN_DIR . 'includes/admin.php';

/**
 * Load bundled translations.
 */
function gltm_load_textdomain() {
	load_plugin_textdomain(
		'gallery-images-link-updater',
		false,
		dirname( plugin_basename( GLTM_PLUGIN_FILE ) ) . '/languages'
	);
}
add_action( 'init', 'gltm_load_textdomain' );

/**
 * Return a supported link destination.
 *
 * @param mixed  $value    Candidate value.
 * @param string $fallback Value used when the candidate is invalid.
 * @return string
 */
function gltm_sanitize_destination( $value, $fallback = 'media' ) {
	$value = is_string( $value ) ? sanitize_key( $value ) : '';

	return in_array( $value, array( 'media', 'attachment', 'none' ), true )
		? $value
		: $fallback;
}

/**
 * Return the configured destination for new Gallery blocks.
 *
 * @return string
 */
function gltm_get_gallery_default_destination() {
	return gltm_sanitize_destination(
		get_option( 'gltm_gallery_default_destination', 'media' )
	);
}

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

	wp_localize_script(
		'gltm-editor',
		'giluEditorSettings',
		array(
			'galleryDestination'   => gltm_get_gallery_default_destination(),
			'linkStandaloneImages' => (bool) get_option( 'gltm_link_standalone_images', false ),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'gltm_enqueue_block_editor_assets' );
