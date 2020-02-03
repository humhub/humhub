# Theme Migration to HumHub 1.4

Please [rebuild](css.md#compile-css-package) and test your theme prior to a production update. 
Even though there were no major theme related changes introduced in HumHub 1.4, you may have to test overwritten views
and new UI features. The following migration guide lists some changes as well as new features which may affect your custom theme.

> Note: In case you notice other problems with your theme, please let us know in the [community](https://community.humhub.com/) 
> or on [github](https://github.com/humhub/humhub/blob/master/protected/humhub/assets/ProsemirrorEditorAsset.php)!

## Page Icon

You can now upload your page icon under `Administration -> Settings -> Appearance -> Icon upload`. Your icon should have
a minimum `height` and `width` of `256px` and one of the following formats: `png`, `jpg`.

## PWA

HumHub 1.4 comes with a build in PWA support. In case you've manually added some of the following head or meta tags, please
remove them from your theme, they will be added automatically.

**Remove all icon and web app related head tags such as**:

- `<link rel="apple-touch-icon" sizes="57x57" href="<?= $this->theme->getBaseUrl(); ?>/ico/apple-icon-57x57.png">`
- `<link rel="icon" type="image/png" sizes="192x192" href="<?= $this->theme->getBaseUrl(); ?>/ico/android-icon-192x192.png">`
- `<link rel="manifest" href="<?= $this->theme->getBaseUrl(); ?>/ico/manifest.json">`

**Remove Metatags**:

- `apple-mobile-web-app-title`
- `apple-mobile-web-app-capable`
- `apple-mobile-web-app-status-bar-style`
- `mobile-web-app-capable`
- `msapplication-TileColor`
- `msapplication-TileImage`
- `msapplication-TileImage`
- `theme-color`
- `application-name`

## View file mapping

The automatic mapping of view files to modified files has been improved with a much more generic approach.
More details can be found in the [Views](views.md) chapter of the theme documentation. 

The old mapping is still supported until at least version 1.5. But we recommend to upgrade your theme as soon as possible.

## Icon abstraction

HumHub 1.4 comes with an icon abstraction widget. Direct usage of font awesome classes are deprecated.

Instead of `<i class="fa fa-arrow-up"></i>` use:

```
<?= Icon::get('arrow-up')?>
```

Other examples:

```
<?= Button::info()->icon('cloud-upload'); ?>
```

## Oembed styles

Version 1.4 enhanced the oembed styling, now you can use following styles in order to align your oembeds:

```
.oembed_snippet[data-oembed-provider="twitter.com"] {
    padding-bottom:0 !important;
    padding-top:0;
    margin-top: 0;
}
```

## CSP Rules

HumHub 1.4 added a default [Content Security Policy Header](../admin/security.md#web-security-configuration). 
Even tough the default header is not very strict, you should test external theme resources as scripts, 
style sheets or fonts of your custom theme.

### print.less

There is a new `print.less` less file which fixes https://github.com/humhub/humhub/issues/3810 and includes further
print style enhancements. As with all included less files you can omit this by setting the `@prev-print` less variable to true.
There are some further print style enhancements added to `stream.less` and `profile.less`

## Space and Profile Header refactoring

The space and profile header has been refactored, old views should still work, but should be migrated to the latest
version. You can now define the banner and profile image size and ratio by means of the following theme variables:

- `space-profile-banner-ratio` default `6.3`
- `space-profile-banner-crop` default `0, 0, 267, 48`

- `space-profile-image-ratio` default `1`
- `space-profile-image-crop` default `0, 0, 100, 100`

- `user-profile-banner-ratio` default `6.3`
- `user-profile-banner-crop` default `0, 0, 267, 48`

- `user-profile-image-ratio` default `1`
- `user-profile-image-crop` default `0, 0, 100, 100`


## Reloadable scripts

You can now configure scripts which should be reloadable by means of the `humhub\modules\admin\Module:$defaultReloadableScripts`
[module configuration](../admin/advanced-configuration.md#module-configuration) as follows:

```php
return [
    'modules' => [
        'admin' => [
            'defaultReloadableScripts' => [ 
                'https://platform.twitter.com/widgets.js'
            ]
        ]
    ]
]
```

## Mobile enhancements

There were many mobile view enhancements and fixes introduced in HumHub 1.4, therefore test your theme on a mobile a
device after the theme rebuild.

### Mobile swipe

The mobile view now supports a left swipe in order to display the sidebar. This behavior can be deactivated under
`Administration -> Appearance -> Use the default swipe to show sidebar on a mobile device`.

## Minor changes overview

- Chng:Removed `jquery-placeholder` asset and dependency
- Chng: Removed `atwho` asset and dependency
- Chng: Removed old IE support
- Chng: Added `jquery.atwho.modified.css` to `mentioning.less`
- Chng: Minor changes in `notification/views/index.php`
- Chng: Refactored `widget/views/panelMenu.php`
- Chng: Use of new WallEntryControls in `modules/content/widgets/views/wallEntry.php`
- Chng: Removed `static/temp.css`, moved required form style to `form.less`
- Chng: Removed italic text from summary mail of comment and content activities for better readability
- Enh: Editable `['twemoji']['path']` config parameter
- Enh: Added isFluid LESS variable for automatic HTML container handling
- Enh: Added show password feature for password form elements
- Enh: Use of colored required input field asterisk

