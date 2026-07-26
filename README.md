# Gallery Images Link Updater

Gallery Images Link Updater sets link defaults for native WordPress Gallery blocks and can optionally link new standalone Image blocks to their media files.

The plugin stores standard `core/gallery` and `core/image` attributes and markup. It has no frontend runtime dependency and does not include a lightbox.

## Scope

- Choose Media File, Attachment Page, or None for newly populated Galleries.
- Optionally link new standalone Image blocks to Media File.
- Handle Image-to-Gallery transformations using the configured Gallery default.
- Update only unlinked Galleries or deliberately overwrite all Galleries with a selected destination.
- Dry-run, update, back up, roll back, and clean up existing Gallery blocks from **Tools → Gallery Images Link Updater**.
- Use the bundled Spanish (Spain) interface translation.
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
