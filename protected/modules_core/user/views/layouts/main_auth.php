<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <!-- end: Meta -->

    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <!-- end: Mobile Specific -->

    <?php $ver = HVersion::VERSION; ?>

    <!-- start: CSS -->
    <link href="<?php echo Yii::app()->baseUrl; ?>/css/animate.min.css?ver=<?php echo $ver; ?>" rel="stylesheet">
    <link href="<?php echo Yii::app()->baseUrl; ?>/css/bootstrap.min.css?ver=<?php echo $ver; ?>" rel="stylesheet">
    <link href="<?php echo Yii::app()->baseUrl; ?>/css/style.css?ver=<?php echo $ver; ?>" rel="stylesheet">
    <link
        href="<?php echo Yii::app()->baseUrl; ?>/resources/font-awesome/css/font-awesome.min.css?ver=<?php echo $ver; ?>"
        rel="stylesheet">
    <link href="<?php echo Yii::app()->baseUrl; ?>/css/flatelements.css?ver=<?php echo $ver; ?>" rel="stylesheet">
    <!-- end: CSS -->

    <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="<?php echo Yii::app()->baseUrl; ?>/js/html5shiv.js"></script>
    <link id="ie-style" href="<?php echo Yii::app()->baseUrl; ?>/css/ie.css" rel="stylesheet">
    <![endif]-->

    <!--[if IE 9]>
    <link id="ie9style" href="<?php echo Yii::app()->baseUrl; ?>/css/ie9.css" rel="stylesheet">
    <![endif]-->

    <!-- start: JavaScript -->
    <script type="text/javascript"
            src="<?php echo Yii::app()->baseUrl; ?>/js/bootstrap.min.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript"
            src="<?php echo Yii::app()->baseUrl; ?>/js/modernizr.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript"
            src="<?php echo Yii::app()->baseUrl; ?>/js/jquery.cookie.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript"
            src="<?php echo Yii::app()->baseUrl; ?>/js/jquery.flatelements.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript"
            src="<?php echo Yii::app()->baseUrl; ?>/js/jquery.placeholder.js?ver=<?php echo $ver; ?>"></script>

    <!-- start: render additional head (css and js files) -->
    <?php $this->renderPartial('application.views.layouts.head'); ?>
    <!-- end: render additional head -->

    <!-- Global app functions -->
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/app.js?ver=<?php echo $ver; ?>"></script>
    <!-- end: JavaScript -->

    <!-- start: Favicon and Touch Icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144"
          href="<?php echo Yii::app()->baseUrl; ?>/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114"
          href="<?php echo Yii::app()->baseUrl; ?>/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72"
          href="<?php echo Yii::app()->baseUrl; ?>/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed"
          href="<?php echo Yii::app()->baseUrl; ?>/ico/apple-touch-icon-57-precomposed.png">
    <link rel="shortcut icon" href="<?php echo Yii::app()->baseUrl; ?>/ico/favicon.ico">
    <!-- end: Favicon and Touch Icons -->


</head>

<body class="login-container">

<!-- start: show content (and check, if exists a sublayout -->
<?php if (isset($this->subLayout) && $this->subLayout != "") : ?>
    <?php echo $this->renderPartial($this->subLayout, array('content' => $content)); ?>
<?php else: ?>
    <?php echo $content; ?>
<?php endif; ?>
<!-- end: show content -->


<script type="text/javascript">

    // Replace the standard checkbox and radio buttons
    $('body').find(':checkbox, :radio').flatelements();

</script>

<?php echo HSetting::GetText('trackingHtmlCode'); ?>

</body>
</html>
