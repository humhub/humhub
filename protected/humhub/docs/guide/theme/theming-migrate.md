# Theming Migration Guide

Here you will learn how you can adapt existing themes to work with the latest HumHub versions.

## Migrate to 1.2

### Use of new Theming

HumHub 1.2 introduced an enhanced theming structure, which allows you to overwrite only the style definitions you want to change. This leads to smaller and cleaner themes without the need to manually maintain each small style change or addition of a new release. Please refer to the  [Theming Guide](theming-index.md) for more details about the new theme structure.

> Note: You can still use your old theme, but you'll have to maintain your theme manually as before. You should at least create your themes `variables.less` file as described in the following, since these variables are used within your mails.

The steps below, describe how to merge your old theme to the new theming structure:

1. Download HumHub 1.2.
2. Copy your theme folder to `humhub\themes` of your HumHub 1.2 project folder.
3. Copy the following directory into your theme `humhub\themes\HumHub\less`.
4. Seperate your theme variables into `humhub\themes\yourTheme\less\variables.less`. Use the new variable names used in `humhub\static\less\variables.less`.
5. Ideally, only add the differences between your theme and the default theme to `humhub\themes\yourTheme\less\theme.less`.
6. Build your theme with `lessc -x build.less ../css/theme.css`.

If your theme overwrites major parts of the defalt theme, you can disable the import of some default less files by setting variables as for example `@prev-login: true;` to disable the import of `humhub/static/less/login.less`. Please see the [Theming Guide](theming-index.md) for more information about this technique.

> Info: You should rebuild your theme after each HumHub release to adopt new theme changes automatically.

> Info: If your theme directory resides outside the `themes` directory, you'll have to edit the `@HUMHUB` variable within the `build.less` file to point to the `static/less` directory of your HumHub v1.2 directory.

### Stream

The new stream javascript rewrite requires some additional data-* attributes, which have to be added in case your theme overwrites one of the mentioned files.

Please check the following files for changes, in case your theme does overwrite those files:

- protected/humhub/modules/stream/widget/views/stream.php
- protected/humhub/modules/content/views/layouts/wallEntry.php

The same applies to the activity stream:

- protected/humhub/modules/activity/widget/views/activityStream.php
- protected/humhub/modules/activity/views/layouts/web.php

### Legacy Themes

Old themes, should check the following file for changes:

- humhub/themes/HumHub/css/theme.deprecated.less

> Note: This file will not be maintained in the future.

### Layout (Pjax)

- Add 'top-menu-nav' to main.php layout.

### Gallery

The old **ekko lighbox** was replaced by the [blueimp ](https://blueimp.github.io/Gallery/) gallery. If your theme
does overwrite a view with gallery images, you'll have to use the new **data-ui-gallery** attribute instead of the
**data-toggle** and **data-gallery** attributes. Please check the following files:

- modules/file/widgets/views/showFiles.php
- modules/space/widgets/views/header.php
- modules/tour/views/tour/welcome.php
- modules/user/widgets/views/profileHeader.php

### JS Rewrite: 

The JS Rewrite removed many inline script blocks from views and uses the new Javascript Module System with data-* attributes. Many UI Components and Widget had been rewritten.

- **General Rewrite**:
    - modules/like/widget/views/likeLink.php 
    - modules/admin/views/setting/design.php 
    - modules/space/views/create/invite.php
    - modules/space/views/membership/invite.php
    - modules/comment/widget/views/showComment.php
    - modules/files/widget/views/showFiles.php
    - modules/post/widget/views/post.php
- **Richtext rewrite**:
    - modules/comment/widget/views/form.php
    - modules/comment/views/comment/edit.php
    - modules/post/views/post/edit.php
    - modules/post/widget/views/form.php
- **UserPicker rewrite**:
    - modules/admin/views/group/edit.php
    - modules/admin/views/group/members.php
    - modules/content/widgets/views/wallCreateContentForm.php
- **TabbedForm**:
    - modules/admin/views/user/add.php 
    - modules/admin/views/user/edit.php 
    - modules/user/views/account/_userProfileLayout.php 
    - modules/user/views/account/_userSettingsLayout.php 
- **Space Picker**
    - modules/admin/views/group/edit.php
    - modules/admin/views/setting/basic.php
    - modules/search/views/search/index.php
- **Refactored**:
    - modules/search/views/search/index.php 
- **Pjax**
    - modules/tour/widgets/views/tourPanel.php
- **Notification**:
   - modules/notification/widget/views/overview.php 

## Migrate to 1.1

- Make sure to update your themed less file with the latest version.
In the upcoming 1.2.x releases we'll split the 'theme' less file into multiple files, to simplify this process.

- There were also many view file updates this release, please check changes (e.g. diff) of your themed versions.
We are constantly reducing view complexity to ease this process.

**Important changed views:**

- Logins (Standalone / Modal)
- Registration
- Main Layout

## Migrate from 0.20 to 1.0

The following line was added to the HumHub Base Theme Less/Css file due to a Bootstrap update:
https://github.com/humhub/humhub/blob/0a388d225a53fd873773cf0989d6e10aaf66996a/themes/HumHub/css/theme.less#L648

## Migrate from 0.11 to 0.20

As you know, HumHub based on the Yii Framework. In the new 0.20 release, the Framework was changed from Yii 1.1 to Yii 2. With this change the style.css in **webroot/css/** was removed and from now all styles are merged in the theme.css under  **webroot/themes/humhub/css/**.

Follow this steps to migrate an older theme ot 0.20:

1. Get the latest **style.css** [here](https://github.com/humhub/humhub/blob/v0.11/css/style.css) and copy it to **webroot/themes/yourtheme/css/**

2. Open the file ``head.php`` in **/themes/yourtheme/views/layouts/**

3. Remove this code snippet:
``
<?php $ver = HVersion::VERSION; ?>
``

4. To load the old **style.css**, insert this code to the first line:
``
<link href="<?php echo $this->theme->getBaseUrl() . '/css/style.css'; ?>" rel="stylesheet">
``

5. Change the structure of all reference calls for your additional theme files from 
``<link href="<?php echo Yii::app()->theme->baseUrl; ?>/css/theme.css?ver=<?php echo $ver; ?>" rel="stylesheet">`` to ``<link href="<?php echo $this->theme->getBaseUrl() . '/css/theme.css'; ?>" rel="stylesheet">``. 

6. Check if everything works well, and fix optical issues at your theme file, if necessery.

## Migrate from 0.9 to 10.0

In 0.10 release, all refer links from **head.php** (to js, css and icon files) moved back to the ``<head>`` section in **main.php**, to keep them independet from themes.

So please edit your **head.php** in ``/themes/yourtheme/views/layouts/`` and make sure that there are only refer to files in your theme folder. (otherwise they are loaded twice and can't work properly)

