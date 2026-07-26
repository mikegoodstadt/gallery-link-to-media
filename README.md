# Gallery Link to Media

Gallery Link to Media makes images in native WordPress Gallery blocks link to their media files by default.

The plugin stores standard `core/gallery` and `core/image` attributes and markup. It has no frontend runtime dependency and does not include a lightbox.

## Scope

- Apply media-file links when images are first added to a Gallery with no explicit link choice.
- Preserve explicit Gallery link choices.
- Handle Image-to-Gallery transformations.
- Scan, dry-run, convert, back up, roll back, and clean up existing Gallery blocks.
- Package installable release ZIP files from version tags.

## Development

This repository is the plugin directory itself. Install it at:

```text
wp-content/plugins/gallery-link-to-media
```

## Release process

1. Update the version in `gallery-link-to-media.php`, `readme.txt`, and `CHANGELOG.md`.
2. Commit the release.
3. Create and push a version tag, for example:

```bash
git tag v0.1.0
git push origin main
git push origin v0.1.0
```

The GitHub Actions workflow builds `gallery-link-to-media.zip` and attaches it to the GitHub release.

## License

GPL-2.0-or-later.
