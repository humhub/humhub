# Theme Migration to HumHub 1.3

## Space & Profile Layouts

The sidebars are now moved into own files `_sidebar.php` view files.

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