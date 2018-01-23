Custom Assets
=============

Structure
---------

Put your custom assets (e.g. images, fonts or javascripts) directly in the theme base directory.

Example:

- /themes/mytheme/img (Images)
- /themes/mytheme/js (Javascript files)
- /themes/mytheme/css (CSS Stylesheets)
- /themes/mytheme/font (Fonts)



Usage
------

You can access the assets using the [[humhub/components/theme]] component.

Example:

```
<a href="<?= Url::to(['/']); ?>"><img src="<?= $this->theme->getBaseUrl() . '/img/mylogo.png'; ?>" alt="My logo"></a>
```


Javascript and Stylesheets
---------------------------

In order to load additional **CSS** or **JavaScript** files in your theme, add them to `/themes/mytheme/views/layouts/head.php`

e.g.

```
<link href="<?= $this->theme->getBaseUrl()  . '/font/nexa/typography.css'; ?>" rel="stylesheet">
```

Overwrite default images
------------------------

You can also overwrite default images (stored in /static/img/) by placing a custom image with the same file name in your theme image directory.

If you want to replace the default user image as example, you need to create a file called **default_user.jpg** in your `/theme/mytheme/img` directory.

