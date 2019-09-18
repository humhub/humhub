# Theme Migration to HumHub 1.4

- Added jquery.atwho.modified.css to `mentioning.less`
- Minor changes in `notification/views/index.php`


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


