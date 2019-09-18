<?php

use humhub\libs\Html;

?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title><?= Yii::t('WebModule.pwa', 'Unable to connect to {site}', ['{site}' => Html::encode(Yii::$app->name)]); ?></title>
    <style type="text/css">
        body {
            background: <?= Yii::$app->view->theme->variable('primary') ?>;
            color: #ffffff;
            font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
            font-weight: 300;
        }

        body a {
            color: #ffffff;
        }

        #content {
            text-align: center;
            margin-top: 15%;
        }

        #content p {
            line-height: 30px;
        }

        #content .smilie {
            font-size: 60px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div id="content">
    <span class="smilie">:-(</span>
    <h1><?= Yii::t('WebModule.pwa', 'Hm...'); ?></h1>
    <h2><?= Yii::t('WebModule.pwa', 'Unable to connect to {site}', ['{site}' => Html::encode(Yii::$app->name)]); ?></h2>
    <p><?= Yii::t('WebModule.pwa', 'Please check your internet connection and <a href="?">refresh</a> this page once your are online again!'); ?></p>
</div>
</body>
</html>
