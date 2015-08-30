# Theming Migration Guide

Here you will learn how you can adapt existing themes to working fine with actually versions.

## Migrate from 0.11.x to 0.20

Do the following changes at ``head.php`` in **/themes/yourtheme/views/layouts/**:

1. Remove this code snippet:
``
<?php $ver = HVersion::VERSION; ?>
``

2. Change the reference call to additional theme files from:

2. The reference call to additional theme files has changed from 
``<link href="<?php echo Yii::app()->theme->baseUrl; ?>/css/theme.css?ver=<?php echo $ver; ?>" rel="stylesheet">`` to ``<link href="<?php echo $this->theme->getBaseUrl() . '/css/theme.css'; ?>" rel="stylesheet">``. So replace it for all of your additional theme files.

``Yii::app()->theme->baseUrl`` to ``$this->theme->getBaseUrl()``. So replace all referrals to additional **CSS** and **JavaScript** files.

---

## Migrate from 0.9 and earlier to 10.0

In 0.10 release, all refer links from **head.php** (to js, css and icon files) moved back to the ``<head>`` section in **main.php**, to keep them independet from themes.

So please edit your **head.php** in ``/themes/yourtheme/views/layouts/`` and make sure that there are only refer to files in your theme folder. (otherwise they are loaded twice and can't work properly)

