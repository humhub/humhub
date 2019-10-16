# Theme Migration to HumHub 1.4

- Added jquery.atwho.modified.css to `mentioning.less`
- Minor changes in `notification/views/index.php`
- Refactored `widget/views/panelMenu.php`
- Use of new WallEntryControls in `modules/content/widgets/views/wallEntry.php`

> Note: This list may not be complete and only contains changes which could interfere with your theme in case you overwrote
> specific views. In case you notice other problems with your theme, please let us know in the community!

## PWA

Remove all icon and web app related head tags such as:

- <link rel="apple-touch-icon" sizes="57x57" href="<?= $this->theme->getBaseUrl(); ?>/ico/apple-icon-57x57.png">
- <link rel="icon" type="image/png" sizes="192x192" href="<?= $this->theme->getBaseUrl(); ?>/ico/android-icon-192x192.png">
- <link rel="manifest" href="<?= $this->theme->getBaseUrl(); ?>/ico/manifest.json">


Remove Metatags:

- apple-mobile-web-app-title
- apple-mobile-web-app-capable
- apple-mobile-web-app-status-bar-style
- mobile-web-app-capable
- msapplication-TileColor
- msapplication-TileImage
- msapplication-TileImage
- theme-color
- application-name


## View file mapping

The automatic mapping of view files to modified files has been improved with a much more generic approach.
More details can be found in the [Views](views.md) chapter of the theme documentation. 

The old mapping will be available until at least version 1.5. But we recommend to upgrade your theme as soon as possible.

