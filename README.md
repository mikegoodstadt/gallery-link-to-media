# Gallery Images Link Updater

Gallery Images Link Updater makes images in native WordPress Gallery blocks link to their media files by default.

The plugin stores standard `core/gallery` and `core/image` attributes and markup. It has no frontend runtime dependency and does not include a lightbox.

## Scope

- Apply media-file links when images are first added to a Gallery with no explicit link choice.
- Preserve existing Media File, Attachment Page, and Enlarge on Click choices.
- Handle Image-to-Gallery transformations.
- Scan, dry-run, convert, back up, roll back, and clean up existing Gallery blocks from **Tools → Gallery Images Link Updater**.
- Package installable release ZIP files from version tags.

## Development

This repository is the plugin directory itself. Install it at:

```text
wp-content/plugins/gallery-images-link-updater
```

## Release process

1. Update the version in `gallery-images-link-updater.php`, `readme.txt`, and `CHANGELOG.md`.
2. Commit the release.
3. Create and push a version tag, for example:

```bash
git tag v0.2.0
git push origin main
git push origin v0.2.0
```

The GitHub Actions workflow builds `gallery-images-link-updater.zip` and attaches it to the GitHub release.

## License

GPL-2.0-or-later.
