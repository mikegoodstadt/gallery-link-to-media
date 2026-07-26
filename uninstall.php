<?php
/**
 * Uninstall Gallery Link to Media.
 *
 * Native Gallery and Image block content is intentionally preserved.
 *
 * @package GalleryLinkToMedia
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'gltm_version' );

if ( is_multisite() ) {
	delete_site_option( 'gltm_version' );
}

