# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2026-07-26

### Changed

- Renamed the plugin to Gallery Images Link Updater.
- Renamed the plugin slug, text domain, main file, release archive, and repository references to `gallery-images-link-updater`.
- Expanded the existing-Gallery updater with Media File, Attachment Page, and None targets.
- Added a safe unlinked-only scope and an explicit all-Galleries overwrite scope.

### Added

- Configurable link destination for newly populated Gallery blocks.
- Optional Media File links for newly inserted standalone Image blocks.
- Spanish (Spain) translation and translation template.

### Fixed

- Normalize the localized standalone-Image setting because WordPress passes localized scalar values to JavaScript as strings.
- Preserve standalone Image initialization state when WordPress remounts the Image editor after media selection.

## [0.1.0] - 2026-07-26

### Added

- Initial WordPress plugin scaffold.
- WordPress.org-compatible plugin metadata and readme.
- Uninstall behavior that preserves native block content.
- GitHub Actions release packaging for version tags.
- Media-file links when a new Gallery receives its first images.
- Media-file links for Image-to-Gallery block transformations.
- Recursive dry-run and conversion tools for existing native Gallery blocks.
- One-time content backups, revision capture, rollback, and backup cleanup.
- Atomic Gallery and Image updates for a coherent editor undo history.
- Tag-based release packaging with synchronized plugin versions.
- Detection of WordPress's automatic `none` link state when a Gallery is populated.

### Verified

- New empty Gallery populated from the Media Library.
- Images subsequently added to an existing Gallery.
- Image-to-Gallery block transformation.
- Preservation of explicit Gallery link choices.
- Existing-gallery dry run, conversion, rollback, and cleanup.
- Frontend compatibility with a LightGallery lightbox.
