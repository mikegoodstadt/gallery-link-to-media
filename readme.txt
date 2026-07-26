=== Gallery Images Link Updater ===
Contributors: mikegoodstadt
Tags: gallery, image, media, block editor, lightbox
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sets link destinations for native WordPress Gallery blocks and optionally for standalone Image blocks.

== Description ==

Gallery Images Link Updater helps editors who use lightboxes or otherwise need native WordPress Gallery block images to link directly to their media files.

The plugin is designed to:

* Apply Media File, Attachment Page, or None when images are first added to a new native Gallery block.
* Optionally link newly inserted standalone Image blocks to their media files.
* Handle Image-to-Gallery block transformations.
* Update unlinked or all existing Gallery blocks to a selected destination.
* Scan and update existing Gallery blocks with dry-run, backup, rollback, and cleanup tools.
* Store only standard WordPress `core/gallery` and `core/image` content, so galleries continue working after deactivation or uninstall.
* Provide a bundled Spanish (Spain) translation.

It does not include a lightbox and adds no frontend JavaScript.

== Installation ==

1. Upload the `gallery-images-link-updater` folder to `/wp-content/plugins/`, or install the plugin through the WordPress Plugins screen.
2. Activate Gallery Images Link Updater.
3. Open **Tools -> Gallery Images Link Updater** to configure defaults.
4. Use the dry run before updating existing content.

== Frequently Asked Questions ==

= Does the plugin include a lightbox? =

No. It produces standard media-file links that can be used by a theme or lightbox plugin.

= Does it affect standalone Image blocks? =

Only when the optional standalone Image setting is enabled. Existing standalone Images are not changed automatically.

= What happens if I deactivate or uninstall it? =

Existing gallery content is preserved. The plugin writes standard WordPress block attributes and links, so no runtime dependency remains.

= Will it override a link option selected by an editor? =

New-block defaults do not replace later editor choices. The administration tool updates only unlinked Galleries by default; its explicit All Galleries scope overwrites existing choices.

== Changelog ==

= 1.0.0 =

* Renamed the plugin to Gallery Images Link Updater.
* Choose Media File, Attachment Page, or None for newly populated Galleries.
* Optionally link new standalone Image blocks to Media File.
* Update only unlinked Galleries or deliberately overwrite all Gallery link choices.
* Added Spanish (Spain) translation.
* Retained dry-run, backup, rollback, and cleanup tools.

= 0.1.0 =

* Set native Gallery block images to Media File when a new Gallery is populated.
* Handle Image-to-Gallery block transformations.
* Add dry-run, conversion, backup, rollback, and cleanup tools for existing galleries.
* Preserve explicit Media File, Attachment Page, and Enlarge on Click choices.
