=== Gallery Link to Media ===
Contributors: mikegoodstadt
Tags: gallery, image, media, block editor, lightbox
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Links images in native WordPress Gallery blocks to their media files by default and provides tools to update existing galleries.

== Description ==

Gallery Link to Media helps editors who use lightboxes or otherwise need native WordPress Gallery block images to link directly to their media files.

The plugin is designed to:

* Apply Media File links when images are first added to a new native Gallery block.
* Preserve explicit editor choices such as Attachment Page or Enlarge on Click.
* Handle Image-to-Gallery block transformations.
* Scan and update existing Gallery blocks with dry-run, backup, rollback, and cleanup tools.
* Store only standard WordPress `core/gallery` and `core/image` content, so galleries continue working after deactivation or uninstall.

It does not include a lightbox and adds no frontend JavaScript.

== Installation ==

1. Upload the `gallery-link-to-media` folder to `/wp-content/plugins/`, or install the plugin through the WordPress Plugins screen.
2. Activate Gallery Link to Media.
3. Add images to a native Gallery block. New galleries without an explicit link choice will use Media File links.
4. To review existing content, use the migration tool under Tools.

== Frequently Asked Questions ==

= Does the plugin include a lightbox? =

No. It produces standard media-file links that can be used by a theme or lightbox plugin.

= Does it affect standalone Image blocks? =

No. Automatic defaults apply only to Image blocks inside a native WordPress Gallery block.

= What happens if I deactivate or uninstall it? =

Existing gallery content is preserved. The plugin writes standard WordPress block attributes and links, so no runtime dependency remains.

= Will it override a link option selected by an editor? =

No. Once a Gallery uses Media File, Attachment Page, or Enlarge on Click, the plugin leaves that choice alone. WordPress automatically writes None when images are first added; the plugin replaces that initial unlinked state with Media File.

== Changelog ==

= 0.1.0 =

* Initial development release.
