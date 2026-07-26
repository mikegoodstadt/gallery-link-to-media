/**
 * Apply media-file links to newly populated native Gallery blocks.
 *
 * The saved content remains entirely native WordPress block markup. This script
 * runs only in the block editor and does not provide frontend behavior.
 */
( function ( blocks, blockEditor, compose, data, element, hooks ) {
	'use strict';

	var GALLERY_BLOCK = 'core/gallery';
	var IMAGE_BLOCK = 'core/image';
	var MEDIA_DESTINATION = 'media';

	/**
	 * Return media-link attributes for an Image block.
	 *
	 * @param {Object} attributes Image block attributes.
	 * @return {Object} Attributes to merge into the Image block.
	 */
	function getMediaLinkAttributes( attributes ) {
		var attachment =
			attributes.id &&
			data.select( 'core' ).getMedia( attributes.id );
		var url =
			( attachment && attachment.source_url ) ||
			attributes.fullUrl ||
			attributes.url;
		var updates = {
			linkDestination: MEDIA_DESTINATION
		};

		if ( url ) {
			updates.href = url;
		}

		return updates;
	}

	/**
	 * Apply a Gallery-wide media-link choice to its current Image children.
	 *
	 * @param {string} clientId Gallery client ID.
	 * @param {Array}  images   Nested Image blocks.
	 */
	function setGalleryImagesToMedia( clientId, images ) {
		var editorDispatch = data.dispatch( 'core/block-editor' );
		var childIds = [];
		var childUpdates = {};

		images.forEach( function ( image ) {
			childIds.push( image.clientId );
			childUpdates[ image.clientId ] = getMediaLinkAttributes(
				image.attributes
			);
		} );

		editorDispatch.updateBlockAttributes( clientId, {
			linkTo: MEDIA_DESTINATION
		} );

		if ( childIds.length ) {
			editorDispatch.updateBlockAttributes(
				childIds,
				childUpdates,
				{ uniqueByBlock: true }
			);
		}
	}

	/**
	 * Watch an individual Gallery for its first Image children.
	 *
	 * Existing populated galleries are deliberately ignored here; the migration
	 * tool handles them explicitly. Once an editor chooses any Gallery link
	 * destination, this initializer also stays out of the way.
	 */
	var withGalleryMediaDefault = compose.createHigherOrderComponent(
		function ( BlockEdit ) {
			return function GalleryMediaDefault( props ) {
				var previousImageCount = element.useRef( null );
				var innerImages = data.useSelect(
					function ( select ) {
						if ( props.name !== GALLERY_BLOCK ) {
							return [];
						}

						var gallery = select( 'core/block-editor' ).getBlock(
							props.clientId
						);

						if ( ! gallery ) {
							return [];
						}

						return gallery.innerBlocks.filter( function ( block ) {
							return block.name === IMAGE_BLOCK;
						} );
					},
					[ props.clientId, props.name ]
				);

				element.useEffect(
					function () {
						if ( props.name !== GALLERY_BLOCK ) {
							return;
						}

						if ( previousImageCount.current === null ) {
							previousImageCount.current = innerImages.length;
							return;
						}

						var gainedFirstImages =
							previousImageCount.current === 0 &&
							innerImages.length > 0;

						previousImageCount.current = innerImages.length;

						if (
							gainedFirstImages &&
							! props.attributes.linkTo
						) {
							setGalleryImagesToMedia(
								props.clientId,
								innerImages
							);
						}
					},
					[
						innerImages,
						props.attributes.linkTo,
						props.clientId,
						props.name
					]
				);

				return element.createElement( BlockEdit, props );
			};
		},
		'withGalleryMediaDefault'
	);

	hooks.addFilter(
		'editor.BlockEdit',
		'gallery-link-to-media/default-new-gallery',
		withGalleryMediaDefault
	);

	/**
	 * Normalize an Image-to-Gallery transformation.
	 *
	 * This is separate from the first-image watcher because transformed Gallery
	 * blocks can be mounted with their Image children already present.
	 *
	 * @param {Object|Array} transformedBlock Result of the core transform.
	 * @param {Array}        originalBlocks   Blocks supplied to the transform.
	 * @return {Object|Array} Filtered transform result.
	 */
	function setTransformedGalleryToMedia(
		transformedBlock,
		originalBlocks
	) {
		if (
			Array.isArray( transformedBlock ) ||
			! transformedBlock ||
			transformedBlock.name !== GALLERY_BLOCK ||
			! Array.isArray( originalBlocks ) ||
			originalBlocks.length !== 1 ||
			originalBlocks[ 0 ].name !== IMAGE_BLOCK
		) {
			return transformedBlock;
		}

		transformedBlock.attributes = Object.assign(
			{},
			transformedBlock.attributes,
			{ linkTo: MEDIA_DESTINATION }
		);

		transformedBlock.innerBlocks = transformedBlock.innerBlocks.map(
			function ( image ) {
				if ( image.name !== IMAGE_BLOCK ) {
					return image;
				}

				image.attributes = Object.assign(
					{},
					image.attributes,
					getMediaLinkAttributes( image.attributes )
				);

				return image;
			}
		);

		return transformedBlock;
	}

	hooks.addFilter(
		'blocks.switchToBlockType.transformedBlock',
		'gallery-link-to-media/image-to-gallery',
		setTransformedGalleryToMedia
	);
} )(
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.compose,
	window.wp.data,
	window.wp.element,
	window.wp.hooks
);
