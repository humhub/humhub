<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="language" content="en"/>

    <link type="text/css" href="<?php echo Yii::app()->baseUrl; ?>/css/animate.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->baseUrl; ?>/css/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->baseUrl; ?>/css/style.css"/>
    <link href="<?php echo Yii::app()->baseUrl; ?>/themes/HumHub/css/theme.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css"
          href="<?php echo Yii::app()->baseUrl; ?>/resources/font-awesome/css/font-awesome.min.css">

    <title><?php echo CHtml::encode($this->pageTitle); ?></title>

</head>
<body>

<div class="container installer" style="margin: 0 auto; max-width: 700px;">
    <div class="logo">
        <?php if (Yii::app()->name == "HumHub") : ?>
            <a class="animated fadeIn" href="http://www.humhub.org" target="_blank" class="">
                <img src="<?php echo $this->module->assetsUrl; ?>/humhub-logo.png" alt="Logo">
            </a>
        <?php else : ?>
            <h1 class="animated fadeIn"><?php echo CHtml::encode(Yii::app()->name); ?></h1>
        <?php endif; ?>
    </div>
    <?php echo $content; ?>
    <div class="text text-center powered">
        Powered by <a href="http://www.humhub.org" target="_blank">HumHub</a>
        <br>
        <br>
    </div>
</div>

<div class="clear"></div>

</body>
</html>


