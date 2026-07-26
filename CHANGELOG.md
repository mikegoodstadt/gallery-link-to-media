# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Changed

- Renamed the plugin to Gallery Images Link Updater.
- Renamed the plugin slug, text domain, main file, release archive, and repository references to `gallery-images-link-updater`.

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
