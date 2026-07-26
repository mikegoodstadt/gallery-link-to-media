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
		var blockIds = [ clientId ];
		var blockUpdates = {};

		blockUpdates[ clientId ] = {
			linkTo: MEDIA_DESTINATION
		};

		images.forEach( function ( image ) {
			blockIds.push( image.clientId );
			blockUpdates[ image.clientId ] = getMediaLinkAttributes(
				image.attributes
			);
		} );

		editorDispatch.updateBlockAttributes(
			blockIds,
			blockUpdates,
			{ uniqueByBlock: true }
		);
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
			function GalleryMediaDefaultControl( props ) {
				var previousImageCount = element.useRef( null );
				var startedEmptyWithoutLink = element.useRef( null );
				var galleryState = data.useSelect(
					function ( select ) {
						var editorSelect = select( 'core/block-editor' );
						var gallery = editorSelect.getBlock( props.clientId );

						return {
							innerImages: gallery
								? gallery.innerBlocks.filter(
										function ( block ) {
											return block.name === IMAGE_BLOCK;
										}
								  )
								: [],
							wasJustInserted:
								editorSelect.wasBlockJustInserted(
									props.clientId,
									'inserter_menu'
								)
						};
					},
					[ props.clientId ]
				);
				var innerImages = galleryState.innerImages;

				if ( startedEmptyWithoutLink.current === null ) {
					startedEmptyWithoutLink.current =
						innerImages.length === 0 &&
						! props.attributes.linkTo;
				}

				element.useEffect(
					function () {
						if ( previousImageCount.current === null ) {
							previousImageCount.current = innerImages.length;

							if (
								innerImages.length > 0 &&
								galleryState.wasJustInserted &&
								( ! props.attributes.linkTo ||
									props.attributes.linkTo === 'none' )
							) {
								setGalleryImagesToMedia(
									props.clientId,
									innerImages
								);
								startedEmptyWithoutLink.current = false;
							}

							return;
						}

						var gainedFirstImages =
							previousImageCount.current === 0 &&
							innerImages.length > 0;

						previousImageCount.current = innerImages.length;

						if (
							gainedFirstImages &&
							startedEmptyWithoutLink.current
						) {
							setGalleryImagesToMedia(
								props.clientId,
								innerImages
							);
							startedEmptyWithoutLink.current = false;
						}
					},
					[
						innerImages,
						galleryState.wasJustInserted,
						props.attributes.linkTo,
						props.clientId
					]
				);

				return element.createElement( BlockEdit, props );
			}

			return function GalleryMediaDefault( props ) {
				if ( props.name !== GALLERY_BLOCK ) {
					return element.createElement( BlockEdit, props );
				}

				return element.createElement(
					GalleryMediaDefaultControl,
					props
				);
			};
		},
		'withGalleryMediaDefault'
	);

	hooks.addFilter(
		'editor.BlockEdit',
		'gallery-images-link-updater/default-new-gallery',
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
		'gallery-images-link-updater/image-to-gallery',
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
