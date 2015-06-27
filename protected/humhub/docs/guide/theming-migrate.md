# Theming Migration Guide

Here you will learn how you can adapt existing themes to working fine with actually versions.

## Migrate from 0.9 and earlier to 10.0

In 0.10 release, all refer links from **head.php** (to js, css and icon files) moved back to the ``<head>`` section in **main.php**, to keep them independet from themes.

So please edit your **head.php** in ``/themes/yourtheme/views/layouts/`` and make sure that there are only refer to files in your theme folder. (otherwise they are loaded twice and can't work properly)

