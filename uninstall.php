<?php
/**
 * Uninstall Gallery Images Link Updater.
 *
 * Native Gallery and Image block content is intentionally preserved.
 *
 * @package GalleryLinkToMedia
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'gltm_version' );
delete_option( 'gltm_gallery_default_destination' );
delete_option( 'gltm_link_standalone_images' );

if ( is_multisite() ) {
	delete_site_option( 'gltm_version' );
	delete_site_option( 'gltm_gallery_default_destination' );
	delete_site_option( 'gltm_link_standalone_images' );
}

delete_post_meta_by_key( '_gltm_original_post_content' );
delete_post_meta_by_key( '_gltm_original_post_content_date' );
delete_post_meta_by_key( '_gltm_converted_at' );
delete_post_meta_by_key( '_gltm_conversion_report' );
