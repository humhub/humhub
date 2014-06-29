<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="language" content="en"/>

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
        <h1><?php echo Yii::app()->name; ?></h1>
        <h2><?php echo Yii::t('InstallerModule.base', 'Your Social Network Toolbox'); ?></h2>
    </div>

    <?php echo $content; ?>
</div>

<div class="clear"></div>

</body>
</html>


