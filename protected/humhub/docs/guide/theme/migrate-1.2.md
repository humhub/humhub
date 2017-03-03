# Theme Migration to HumHub 1.2

## Use of new Theming

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

## Stream

The new stream javascript rewrite requires some additional data-* attributes, which have to be added in case your theme overwrites one of the mentioned files.

Please check the following files for changes, in case your theme does overwrite those files:

- protected/humhub/modules/stream/widget/views/stream.php
- protected/humhub/modules/content/views/layouts/wallEntry.php

The same applies to the activity stream:

- protected/humhub/modules/activity/widget/views/activityStream.php
- protected/humhub/modules/activity/views/layouts/web.php

## Legacy Themes

Old themes, should check the following file for changes:

- humhub/themes/HumHub/css/theme.deprecated.less

> Note: This file will not be maintained in the future.

## Pjax

In v1.2 we introduced the pjax js library for faster partial page loads. Pjax enables us to navigate through the site without the need of full page loads.
If Pjax is enabled (default) it has an impact on how your javascript is executed. 

In Yii Javascript files are only loaded and executed once per full page load, therefore if you include code which makes use of for example `$(document).ready`, this code is only
executed once if pjax is enabled, even if you navigate to another page.

For this purpose you can either listen to `$(document).on('humhub:ready',...)` which is fired after a full page load and pjax page loads, or preferably implement a
humhub module with an `init` function for your initialization logic.

You can also disable pjax by using the following configuration param in your `protected/config/common.php`:

return [
    'params' => [
        'enablePjax' => false,
    ]
]

> Note: Since pjax provides a major performance boost, you should consider merging your Theme to the new pjax logic.

## Layout

- We cleaned up the themes [main.php](https://github.com/humhub/humhub/blob/master/protected/humhub/views/layouts/main.php)
- Add 'top-menu-nav' to [main.php](https://github.com/humhub/humhub/blob/master/protected/humhub/views/layouts/main.php#L45) layout. This is required for pjax page loads.
- We added the icon definition etc to the themes [head.php](https://github.com/humhub/humhub/blob/master/themes/HumHub/views/layouts/head.php)

## Gallery

The old **ekko lighbox** was replaced by the [blueimp ](https://blueimp.github.io/Gallery/) gallery. If your theme
does overwrite a view with gallery images, you'll have to use the new **data-ui-gallery** attribute instead of the
**data-toggle** and **data-gallery** attributes. Please check the following files:

- modules/file/widgets/views/showFiles.php
- modules/space/widgets/views/header.php
- modules/tour/views/tour/welcome.php
- modules/user/widgets/views/profileHeader.php

## JS Rewrite: 

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