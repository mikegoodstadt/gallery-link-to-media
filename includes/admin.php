<?php
/**
 * Admin migration tools.
 *
 * @package GalleryLinkToMedia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const GLTM_BACKUP_META      = '_gltm_original_post_content';
const GLTM_BACKUP_DATE_META = '_gltm_original_post_content_date';
const GLTM_CONVERTED_META   = '_gltm_converted_at';
const GLTM_REPORT_META      = '_gltm_conversion_report';
const GLTM_NONCE_ACTION     = 'gltm_manage_galleries';

/**
 * Add the migration screen under Tools.
 */
function gltm_admin_menu() {
	add_management_page(
		__( 'Gallery Link to Media', 'gallery-link-to-media' ),
		__( 'Gallery Link to Media', 'gallery-link-to-media' ),
		'manage_options',
		'gallery-link-to-media',
		'gltm_render_admin_page'
	);
}
add_action( 'admin_menu', 'gltm_admin_menu' );

/**
 * Add a shortcut from the Plugins screen.
 *
 * @param array $links Existing action links.
 * @return array
 */
function gltm_plugin_action_links( $links ) {
	array_unshift(
		$links,
		sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'tools.php?page=gallery-link-to-media' ) ),
			esc_html__( 'Scan Galleries', 'gallery-link-to-media' )
		)
	);

	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( GLTM_PLUGIN_FILE ), 'gltm_plugin_action_links' );

/**
 * Render and handle the migration screen.
 */
function gltm_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$result     = null;
	$post_types = gltm_get_default_post_types();
	$post_id    = 0;

	if ( isset( $_POST['gltm_action'] ) ) {
		check_admin_referer( GLTM_NONCE_ACTION );

		$action     = sanitize_key( wp_unslash( $_POST['gltm_action'] ) );
		$post_types = gltm_sanitize_post_types(
			isset( $_POST['gltm_post_types'] )
				? (array) wp_unslash( $_POST['gltm_post_types'] )
				: array()
		);
		$post_id    = isset( $_POST['gltm_post_id'] )
			? absint( wp_unslash( $_POST['gltm_post_id'] ) )
			: 0;

		if ( 'dry_run' === $action ) {
			$result = gltm_process_posts( $post_types, false, $post_id );
		} elseif ( 'convert' === $action ) {
			$result = gltm_process_posts( $post_types, true, $post_id );
		} elseif ( 'rollback' === $action ) {
			$result = gltm_rollback_posts( $post_types, $post_id );
		} elseif ( 'cleanup' === $action ) {
			$result = gltm_cleanup_posts( $post_types, $post_id );
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Gallery Link to Media', 'gallery-link-to-media' ); ?></h1>
		<p><?php esc_html_e( 'Find native Gallery blocks without an explicit link choice and link their images directly to their media files.', 'gallery-link-to-media' ); ?></p>

		<?php if ( $result ) : ?>
			<div class="notice <?php echo empty( $result['errors'] ) ? 'notice-success' : 'notice-warning'; ?>">
				<p><strong><?php echo esc_html( $result['title'] ); ?></strong></p>
				<p><?php echo esc_html( $result['message'] ); ?></p>
			</div>

			<?php if ( $result['stats'] ) : ?>
				<table class="widefat striped" style="max-width:960px;margin:16px 0;">
					<tbody>
						<?php foreach ( $result['stats'] as $label => $value ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( $label ); ?></th>
								<td><?php echo esc_html( number_format_i18n( $value ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<?php if ( $result['items'] ) : ?>
				<h2><?php esc_html_e( 'Posts', 'gallery-link-to-media' ); ?></h2>
				<table class="widefat striped" style="max-width:1200px;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'ID', 'gallery-link-to-media' ); ?></th>
							<th><?php esc_html_e( 'Title', 'gallery-link-to-media' ); ?></th>
							<th><?php esc_html_e( 'Post type', 'gallery-link-to-media' ); ?></th>
							<th><?php esc_html_e( 'Galleries', 'gallery-link-to-media' ); ?></th>
							<th><?php esc_html_e( 'Images', 'gallery-link-to-media' ); ?></th>
							<th><?php esc_html_e( 'Status', 'gallery-link-to-media' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $result['items'] as $item ) : ?>
							<tr>
								<td><?php echo esc_html( $item['id'] ); ?></td>
								<td><a href="<?php echo esc_url( get_edit_post_link( $item['id'] ) ); ?>"><?php echo esc_html( $item['title'] ); ?></a></td>
								<td><code><?php echo esc_html( $item['post_type'] ); ?></code></td>
								<td><?php echo esc_html( $item['galleries'] ); ?></td>
								<td><?php echo esc_html( $item['images'] ); ?></td>
								<td><?php echo esc_html( $item['status'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<?php if ( $result['errors'] ) : ?>
				<h2><?php esc_html_e( 'Warnings', 'gallery-link-to-media' ); ?></h2>
				<ul>
					<?php foreach ( $result['errors'] as $error ) : ?>
						<li><?php echo esc_html( $error ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		<?php endif; ?>

		<form method="post" style="max-width:960px;">
			<?php wp_nonce_field( GLTM_NONCE_ACTION ); ?>

			<h2><?php esc_html_e( 'Post types', 'gallery-link-to-media' ); ?></h2>
			<fieldset>
				<?php foreach ( gltm_get_available_post_types() as $type => $label ) : ?>
					<label style="display:block;margin:0 0 8px;">
						<input type="checkbox" name="gltm_post_types[]" value="<?php echo esc_attr( $type ); ?>" <?php checked( in_array( $type, $post_types, true ) ); ?>>
						<?php echo esc_html( $label ); ?> <code><?php echo esc_html( $type ); ?></code>
					</label>
				<?php endforeach; ?>
			</fieldset>

			<h2><?php esc_html_e( 'Scope', 'gallery-link-to-media' ); ?></h2>
			<p>
				<label for="gltm_post_id"><strong><?php esc_html_e( 'Single post ID', 'gallery-link-to-media' ); ?></strong></label><br>
				<input class="regular-text" id="gltm_post_id" min="1" name="gltm_post_id" step="1" type="number" value="<?php echo $post_id ? esc_attr( $post_id ) : ''; ?>">
			</p>
			<p class="description"><?php esc_html_e( 'Leave blank to scan all selected post types. A post ID overrides the post-type selection.', 'gallery-link-to-media' ); ?></p>

			<p>
				<button class="button button-secondary" name="gltm_action" type="submit" value="dry_run"><?php esc_html_e( 'Dry Run', 'gallery-link-to-media' ); ?></button>
				<button class="button button-primary" name="gltm_action" type="submit" value="convert"><?php esc_html_e( 'Convert Galleries', 'gallery-link-to-media' ); ?></button>
				<button class="button button-secondary" name="gltm_action" type="submit" value="rollback"><?php esc_html_e( 'Rollback Converted Posts', 'gallery-link-to-media' ); ?></button>
				<button class="button button-secondary" name="gltm_action" type="submit" value="cleanup"><?php esc_html_e( 'Delete Conversion Backups', 'gallery-link-to-media' ); ?></button>
			</p>
		</form>

		<div class="card" style="max-width:960px;">
			<h2><?php esc_html_e( 'Conversion policy', 'gallery-link-to-media' ); ?></h2>
			<p><?php esc_html_e( 'Only Gallery blocks whose Gallery-level link destination is unset are converted. Explicit Attachment Page, Media File, Enlarge on Click, and None choices are preserved.', 'gallery-link-to-media' ); ?></p>
			<p><?php esc_html_e( 'Run a dry run and back up the database before converting. Each changed post also receives a one-time content backup for rollback.', 'gallery-link-to-media' ); ?></p>
		</div>
	</div>
	<?php
}

/**
 * Return post types shown in the migration tool.
 *
 * @return array
 */
function gltm_get_available_post_types() {
	$objects = get_post_types( array( 'show_ui' => true ), 'objects' );
	$types   = array();

	foreach ( $objects as $object ) {
		if ( 'attachment' !== $object->name ) {
			$types[ $object->name ] = $object->labels->singular_name;
		}
	}

	asort( $types );

	return $types;
}

/**
 * Return sensible initial selections.
 *
 * @return array
 */
function gltm_get_default_post_types() {
	$defaults = array();

	foreach ( get_post_types( array( 'public' => true ), 'names' ) as $type ) {
		if ( 'attachment' !== $type ) {
			$defaults[] = $type;
		}
	}

	return $defaults;
}

/**
 * Validate submitted post types.
 *
 * @param array $post_types Submitted types.
 * @return array
 */
function gltm_sanitize_post_types( $post_types ) {
	return array_values(
		array_intersect(
			array_map( 'sanitize_key', $post_types ),
			array_keys( gltm_get_available_post_types() )
		)
	);
}

/**
 * Scan or update matching posts.
 *
 * @param array $post_types Types to scan.
 * @param bool  $apply      Whether to save changes.
 * @param int   $post_id    Optional single post.
 * @return array
 */
function gltm_process_posts( $post_types, $apply, $post_id = 0 ) {
	$result = gltm_empty_result();
	$ids    = gltm_get_candidate_post_ids( $post_types, $post_id );

	$result['title'] = $apply
		? __( 'Conversion complete', 'gallery-link-to-media' )
		: __( 'Dry run complete', 'gallery-link-to-media' );

	foreach ( $ids as $id ) {
		$post = get_post( $id );
		if ( ! $post ) {
			continue;
		}

		$conversion = gltm_convert_content( $post->post_content );
		if ( ! $conversion['galleries'] ) {
			continue;
		}

		$status = __( 'Would convert', 'gallery-link-to-media' );

		if ( $apply && $conversion['changed'] ) {
			if ( ! metadata_exists( 'post', $id, GLTM_BACKUP_META ) ) {
				update_post_meta( $id, GLTM_BACKUP_META, $post->post_content );
				update_post_meta( $id, GLTM_BACKUP_DATE_META, current_time( 'mysql' ) );
			}

			if ( post_type_supports( $post->post_type, 'revisions' ) ) {
				wp_save_post_revision( $id );
				++$result['stats'][ __( 'Revisions saved', 'gallery-link-to-media' ) ];
			}

			$updated = wp_update_post(
				wp_slash(
					array(
						'ID'           => $id,
						'post_content' => $conversion['content'],
					)
				),
				true
			);

			if ( is_wp_error( $updated ) ) {
				$status             = __( 'Error', 'gallery-link-to-media' );
				$result['errors'][] = sprintf(
					/* translators: 1: post ID, 2: error message. */
					__( 'Post %1$d could not be updated: %2$s', 'gallery-link-to-media' ),
					$id,
					$updated->get_error_message()
				);
			} else {
				$status = __( 'Converted', 'gallery-link-to-media' );
				update_post_meta( $id, GLTM_CONVERTED_META, current_time( 'mysql' ) );
				update_post_meta(
					$id,
					GLTM_REPORT_META,
					array(
						'galleries' => $conversion['galleries'],
						'images'    => $conversion['images'],
					)
				);
				++$result['stats'][ __( 'Posts converted', 'gallery-link-to-media' ) ];
			}
		}

		$result['items'][] = array(
			'id'         => $id,
			'title'      => get_the_title( $id ),
			'post_type'  => $post->post_type,
			'galleries'  => $conversion['galleries'],
			'images'     => $conversion['images'],
			'status'     => $status,
		);
		$result['stats'][ __( 'Posts matched', 'gallery-link-to-media' ) ]++;
		$result['stats'][ __( 'Galleries matched', 'gallery-link-to-media' ) ] += $conversion['galleries'];
		$result['stats'][ __( 'Images matched', 'gallery-link-to-media' ) ] += $conversion['images'];
		$result['errors'] = array_merge( $result['errors'], $conversion['errors'] );
	}

	$result['message'] = $apply
		? __( 'Eligible galleries were linked to their media files.', 'gallery-link-to-media' )
		: __( 'No post content was changed.', 'gallery-link-to-media' );

	return $result;
}

/**
 * Return a standard result structure.
 *
 * @return array
 */
function gltm_empty_result() {
	return array(
		'title'   => '',
		'message' => '',
		'stats'   => array(
			__( 'Posts matched', 'gallery-link-to-media' )     => 0,
			__( 'Galleries matched', 'gallery-link-to-media' ) => 0,
			__( 'Images matched', 'gallery-link-to-media' )    => 0,
			__( 'Posts converted', 'gallery-link-to-media' )   => 0,
			__( 'Revisions saved', 'gallery-link-to-media' )   => 0,
		),
		'items'   => array(),
		'errors'  => array(),
	);
}

/**
 * Convert eligible Gallery blocks in content.
 *
 * @param string $content Post content.
 * @return array
 */
function gltm_convert_content( $content ) {
	$result = array(
		'content'   => $content,
		'changed'   => false,
		'galleries' => 0,
		'images'    => 0,
		'errors'    => array(),
	);
	$blocks = gltm_convert_blocks( parse_blocks( $content ), $result );

	if ( $result['changed'] ) {
		$result['content'] = serialize_blocks( $blocks );
	}

	return $result;
}

/**
 * Convert parsed blocks recursively.
 *
 * @param array $blocks Parsed blocks.
 * @param array $result Conversion result by reference.
 * @return array
 */
function gltm_convert_blocks( $blocks, &$result ) {
	foreach ( $blocks as &$block ) {
		if ( 'core/gallery' === $block['blockName'] && ! isset( $block['attrs']['linkTo'] ) ) {
			$converted_images = 0;

			foreach ( $block['innerBlocks'] as &$image ) {
				if ( gltm_convert_image_block( $image, $result['errors'] ) ) {
					++$converted_images;
				}
			}
			unset( $image );

			if ( $converted_images ) {
				$block['attrs']['linkTo'] = 'media';
				++$result['galleries'];
				$result['images'] += $converted_images;
				$result['changed'] = true;
			}
		} elseif ( ! empty( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = gltm_convert_blocks( $block['innerBlocks'], $result );
		}
	}
	unset( $block );

	return $blocks;
}

/**
 * Link an Image block to its full attachment URL.
 *
 * @param array $block  Parsed Image block by reference.
 * @param array $errors Warning list by reference.
 * @return bool
 */
function gltm_convert_image_block( &$block, &$errors ) {
	if ( 'core/image' !== $block['blockName'] ) {
		return false;
	}

	$attachment_id = isset( $block['attrs']['id'] ) ? absint( $block['attrs']['id'] ) : 0;
	$media_url     = $attachment_id ? wp_get_attachment_url( $attachment_id ) : '';

	if ( ! $media_url ) {
		$errors[] = sprintf(
			/* translators: %d: attachment ID. */
			__( 'An Image block with attachment ID %d has no media URL and was skipped.', 'gallery-link-to-media' ),
			$attachment_id
		);
		return false;
	}

	$block['attrs']['linkDestination'] = 'media';
	$block['innerHTML']                = gltm_link_image_markup( $block['innerHTML'], $media_url );

	foreach ( $block['innerContent'] as &$fragment ) {
		if ( is_string( $fragment ) ) {
			$fragment = gltm_link_image_markup( $fragment, $media_url );
		}
	}
	unset( $fragment );

	return true;
}

/**
 * Add or update the anchor surrounding an Image block's image.
 *
 * @param string $markup    Image block markup.
 * @param string $media_url Full attachment URL.
 * @return string
 */
function gltm_link_image_markup( $markup, $media_url ) {
	if ( preg_match( '/<a\b[^>]*>\s*<(?:picture|img)\b/i', $markup ) ) {
		return (string) preg_replace(
			'/(<a\b[^>]*\bhref=)(["\']).*?\2/i',
			'$1$2' . esc_url( $media_url ) . '$2',
			$markup,
			1
		);
	}

	return (string) preg_replace(
		'/(<picture\b.*?<\/picture>|<img\b[^>]*>)/is',
		'<a href="' . esc_url( $media_url ) . '">$1</a>',
		$markup,
		1
	);
}

/**
 * Return posts that may contain native Gallery blocks.
 *
 * @param array $post_types Selected post types.
 * @param int   $post_id    Optional single post.
 * @return array
 */
function gltm_get_candidate_post_ids( $post_types, $post_id = 0 ) {
	global $wpdb;

	if ( $post_id ) {
		return array_map(
			'absint',
			$wpdb->get_col(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE ID = %d AND post_status NOT IN ('trash','auto-draft') AND post_content LIKE %s",
					$post_id,
					'%' . $wpdb->esc_like( 'wp:gallery' ) . '%'
				)
			)
		);
	}

	if ( ! $post_types ) {
		return array();
	}

	$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
	$args         = array_merge(
		$post_types,
		array( '%' . $wpdb->esc_like( 'wp:gallery' ) . '%' )
	);

	return array_map(
		'absint',
		$wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type IN ($placeholders) AND post_status NOT IN ('trash','auto-draft') AND post_content LIKE %s ORDER BY ID ASC",
				$args
			)
		)
	);
}

/**
 * Roll back posts from their one-time content backups.
 *
 * @param array $post_types Selected post types.
 * @param int   $post_id    Optional single post.
 * @return array
 */
function gltm_rollback_posts( $post_types, $post_id = 0 ) {
	$result = gltm_empty_result();
	$ids    = gltm_get_managed_post_ids( $post_types, $post_id );

	$result['title'] = __( 'Rollback complete', 'gallery-link-to-media' );

	foreach ( $ids as $id ) {
		$backup = get_post_meta( $id, GLTM_BACKUP_META, true );
		if ( ! is_string( $backup ) ) {
			continue;
		}

		$updated = wp_update_post(
			wp_slash(
				array(
					'ID'           => $id,
					'post_content' => $backup,
				)
			),
			true
		);

		if ( is_wp_error( $updated ) ) {
			$result['errors'][] = sprintf(
				/* translators: 1: post ID, 2: error message. */
				__( 'Post %1$d could not be restored: %2$s', 'gallery-link-to-media' ),
				$id,
				$updated->get_error_message()
			);
		} else {
			$result['items'][] = gltm_result_item( $id, __( 'Restored', 'gallery-link-to-media' ) );
		}
	}

	$result['stats']   = array( __( 'Posts restored', 'gallery-link-to-media' ) => count( $result['items'] ) );
	$result['message'] = __( 'Original post content was restored where a conversion backup was available.', 'gallery-link-to-media' );

	return $result;
}

/**
 * Delete managed post metadata without changing content.
 *
 * @param array $post_types Selected post types.
 * @param int   $post_id    Optional single post.
 * @return array
 */
function gltm_cleanup_posts( $post_types, $post_id = 0 ) {
	$result = gltm_empty_result();
	$ids    = gltm_get_managed_post_ids( $post_types, $post_id );

	foreach ( $ids as $id ) {
		delete_post_meta( $id, GLTM_BACKUP_META );
		delete_post_meta( $id, GLTM_BACKUP_DATE_META );
		delete_post_meta( $id, GLTM_CONVERTED_META );
		delete_post_meta( $id, GLTM_REPORT_META );
		$result['items'][] = gltm_result_item( $id, __( 'Backups deleted', 'gallery-link-to-media' ) );
	}

	$result['title']   = __( 'Cleanup complete', 'gallery-link-to-media' );
	$result['message'] = __( 'Plugin backup and report metadata was deleted. Post content was not changed.', 'gallery-link-to-media' );
	$result['stats']   = array( __( 'Posts cleaned', 'gallery-link-to-media' ) => count( $ids ) );

	return $result;
}

/**
 * Return post IDs with plugin backups.
 *
 * @param array $post_types Selected post types.
 * @param int   $post_id    Optional single post.
 * @return array
 */
function gltm_get_managed_post_ids( $post_types, $post_id = 0 ) {
	global $wpdb;

	if ( $post_id ) {
		return metadata_exists( 'post', $post_id, GLTM_BACKUP_META ) ? array( $post_id ) : array();
	}

	if ( ! $post_types ) {
		return array();
	}

	$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
	$args         = array_merge( array( GLTM_BACKUP_META ), $post_types );

	return array_map(
		'absint',
		$wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID WHERE pm.meta_key = %s AND p.post_type IN ($placeholders) ORDER BY p.ID ASC",
				$args
			)
		)
	);
}

/**
 * Build a compact result row.
 *
 * @param int    $post_id Post ID.
 * @param string $status  Status label.
 * @return array
 */
function gltm_result_item( $post_id, $status ) {
	$post = get_post( $post_id );

	return array(
		'id'         => $post_id,
		'title'      => get_the_title( $post_id ),
		'post_type'  => $post ? $post->post_type : '',
		'galleries'  => 0,
		'images'     => 0,
		'status'     => $status,
	);
}
