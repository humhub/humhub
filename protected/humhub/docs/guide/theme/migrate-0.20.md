# Theme Migration to HumHub 0.20

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