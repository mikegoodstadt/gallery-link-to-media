/**
 * Apply configured link destinations to newly populated native blocks.
 *
 * The saved content remains entirely native WordPress block markup. This script
 * runs only in the block editor and does not provide frontend behavior.
 */
( function ( compose, data, element, hooks, settings ) {
	'use strict';

	var GALLERY_BLOCK = 'core/gallery';
	var IMAGE_BLOCK = 'core/image';
	var galleryDestination = settings.galleryDestination || 'media';
	var linkStandaloneImages =
		settings.linkStandaloneImages === true ||
		settings.linkStandaloneImages === 1 ||
		settings.linkStandaloneImages === '1';
	var standaloneCandidates = {};

	/**
	 * Return link attributes for an Image block.
	 *
	 * @param {Object} attributes  Image block attributes.
	 * @param {string} destination Link destination.
	 * @return {Object} Attributes to merge into the Image block.
	 */
	function getImageLinkAttributes( attributes, destination ) {
		var attachment =
			attributes.id &&
			data.select( 'core' ).getMedia( attributes.id );
		var href;

		if ( destination === 'media' ) {
			href =
				( attachment && attachment.source_url ) ||
				attributes.fullUrl ||
				attributes.url;
		} else if ( destination === 'attachment' ) {
			href =
				( attachment && attachment.link ) ||
				attributes.href;
		}

		return {
			href: href,
			lightbox: undefined,
			linkDestination: destination
		};
	}

	/**
	 * Apply the configured link choice to a Gallery and its Image children.
	 *
	 * @param {string} clientId   Gallery client ID.
	 * @param {Array}  images     Nested Image blocks.
	 * @param {string} destination Link destination.
	 */
	function setGalleryDestination( clientId, images, destination ) {
		var blockIds = [ clientId ];
		var blockUpdates = {};

		blockUpdates[ clientId ] = {
			linkTo: destination
		};

		images.forEach( function ( image ) {
			blockIds.push( image.clientId );
			blockUpdates[ image.clientId ] = getImageLinkAttributes(
				image.attributes,
				destination
			);
		} );

		data.dispatch( 'core/block-editor' ).updateBlockAttributes(
			blockIds,
			blockUpdates,
			{ uniqueByBlock: true }
		);
	}

	/**
	 * Watch a Gallery for its first Image children.
	 *
	 * Existing populated galleries are ignored; the administration tool handles
	 * them explicitly. WordPress writes `none` while first populating a Gallery,
	 * so the initializer remembers that the Gallery began empty and unset.
	 */
	function GalleryDefaultControl( props ) {
		var previousImageCount = element.useRef( null );
		var startedEmptyWithoutLink = element.useRef( null );
		var galleryState = data.useSelect(
			function ( select ) {
				var editorSelect = select( 'core/block-editor' );
				var gallery = editorSelect.getBlock( props.clientId );

				return {
					innerImages: gallery
						? gallery.innerBlocks.filter( function ( block ) {
								return block.name === IMAGE_BLOCK;
						  } )
						: [],
					wasJustInserted: editorSelect.wasBlockJustInserted(
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
						setGalleryDestination(
							props.clientId,
							innerImages,
							galleryDestination
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
					setGalleryDestination(
						props.clientId,
						innerImages,
						galleryDestination
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

		return element.createElement( props.BlockEdit, props );
	}

	/**
	 * Watch a standalone Image for its first selected attachment.
	 */
	function StandaloneImageDefaultControl( props ) {
		var imageState = data.useSelect(
			function ( select ) {
				var editorSelect = select( 'core/block-editor' );

				return {
					isInGallery:
						editorSelect.getBlockParentsByBlockName(
							props.clientId,
							GALLERY_BLOCK
						).length > 0,
					wasJustInserted: editorSelect.wasBlockJustInserted(
						props.clientId,
						'inserter_menu'
					)
				};
			},
			[ props.clientId ]
		);
		var hasImage = Boolean(
			props.attributes.id || props.attributes.url
		);

		/*
		 * WordPress can remount the Image edit component when an attachment is
		 * selected. Keep the empty-block marker outside the component so it
		 * survives that remount, while existing populated Images remain ignored.
		 */
		if ( ! imageState.isInGallery && ! hasImage ) {
			standaloneCandidates[ props.clientId ] = true;
		}

		element.useEffect(
			function () {
				if ( imageState.isInGallery ) {
					delete standaloneCandidates[ props.clientId ];
					return;
				}

				var shouldApply =
					hasImage &&
					( standaloneCandidates[ props.clientId ] ||
						imageState.wasJustInserted ) &&
					( ! props.attributes.linkDestination ||
						props.attributes.linkDestination === 'none' );

				if ( shouldApply ) {
					data.dispatch(
						'core/block-editor'
					).updateBlockAttributes(
						props.clientId,
						getImageLinkAttributes(
							props.attributes,
							'media'
						)
					);
					delete standaloneCandidates[ props.clientId ];
				}
			},
			[
				hasImage,
				imageState.isInGallery,
				imageState.wasJustInserted,
				props.attributes,
				props.clientId
			]
		);

		return element.createElement( props.BlockEdit, props );
	}

	var withLinkDefaults = compose.createHigherOrderComponent(
		function ( BlockEdit ) {
			return function LinkDefaults( props ) {
				var controlProps = Object.assign(
					{ BlockEdit: BlockEdit },
					props
				);

				if ( props.name === GALLERY_BLOCK ) {
					return element.createElement(
						GalleryDefaultControl,
						controlProps
					);
				}

				if (
					props.name === IMAGE_BLOCK &&
					linkStandaloneImages
				) {
					return element.createElement(
						StandaloneImageDefaultControl,
						controlProps
					);
				}

				return element.createElement( BlockEdit, props );
			};
		},
		'withLinkDefaults'
	);

	hooks.addFilter(
		'editor.BlockEdit',
		'gallery-images-link-updater/default-links',
		withLinkDefaults
	);

	/**
	 * Normalize an Image-to-Gallery transformation.
	 *
	 * @param {Object|Array} transformedBlock Result of the core transform.
	 * @param {Array}        originalBlocks   Blocks supplied to the transform.
	 * @return {Object|Array} Filtered transform result.
	 */
	function setTransformedGalleryDestination(
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
			{ linkTo: galleryDestination }
		);

		transformedBlock.innerBlocks = transformedBlock.innerBlocks.map(
			function ( image ) {
				if ( image.name !== IMAGE_BLOCK ) {
					return image;
				}

				image.attributes = Object.assign(
					{},
					image.attributes,
					getImageLinkAttributes(
						image.attributes,
						galleryDestination
					)
				);

				return image;
			}
		);

		return transformedBlock;
	}

	hooks.addFilter(
		'blocks.switchToBlockType.transformedBlock',
		'gallery-images-link-updater/image-to-gallery',
		setTransformedGalleryDestination
	);
} )(
	window.wp.compose,
	window.wp.data,
	window.wp.element,
	window.wp.hooks,
	window.giluEditorSettings || {}
);
