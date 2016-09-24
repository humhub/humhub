# Theming Migration Guide

Here you will learn how you can adapt existing themes to working fine with actually versions.

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

