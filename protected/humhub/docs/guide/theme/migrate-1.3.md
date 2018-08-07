# Theme Migration to HumHub 1.3

## Automatic stylesheet loading

The file `yourtheme/css/theme.css` is now automatically included into the HTML header.

Please remove following lines from the file `yourtheme/views/layouts/head.php`:

```html
<link href="<?= $this->theme->getBaseUrl() ?>/css/theme.css" rel="stylesheet">
<link href="<?= $this->theme->getBaseUrl() ?>/font/open_sans/open-sans.css" rel="stylesheet">
```

## Parent themes

Please refer to the  [Parent themes section](css.md#parent-themes) for more information about the new `@baseTheme` variable.
Using this technique will ease the update process of your custom theme.

## Space & Profile Layouts and header

The sidebar handling of the content container layouts has changed.

Please check following view files for changes:

- `/protected/humhub/modules/user/views/profile/_layout.php`
- `/protected/humhub/modules/user/views/space/_layout.php`
- `/protected/humhub/modules/user/views/profile/home.php`
- `/protected/humhub/modules/user/views/space/home.php`

Also check the deprecation of `humhub\modules\activity\widgets\Stream` in case you've overwritten
the space or dashboard layout.

Also the space/profile header files (Statistic section) has slightly changed.

Please check following view files for changes:
- `/protected/humhub/modules/user/widgets/views/profileHeader.php`
- `/protected/humhub/modules/space/widgets/views/header.php`


## New Richtext

#### Added wrapper div `comment-create-input-group` to

 - `/protected/humhub/modules/comment/views/comment/edit.php`
 - `/protected/humhub/modules/comment/widgets/views/form.php`

#### Added wrapper div `post-richtext-input-group` to

 - `/protected/humhub/modules/post/views/post/edit.php`
 
#### Minor changes in the following less files:
 
  - `static/less/comment.less`
  - `static/less/file.less`
  - `static/less/mentioning.less`

> Note: Those changes will be included by rebuilding your theme, as long as you did not exclude those files from your theme build.

#### Added `static/less/richtext.less` file.

This file contains the style of the new wysiwyg rich text editor and will be included by a theme rebuild.
As with other less files this file can be excluded from your theme build by less variable `@prev-richtext`.
