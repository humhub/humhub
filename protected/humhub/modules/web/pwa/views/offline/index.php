<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\ui\view\components\View;

/* @var View $this */
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title><?= Yii::t('WebModule.pwa', 'Unable to connect to {site}', ['{site}' => Html::encode(Yii::$app->name)]); ?></title>
    <style type="text/css">
        body {
            background: <?= $this->theme->variable('primary') ?>;
            color: <?= $this->theme->variable('text-color-contrast', '#fff') ?>;
            font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
            font-weight: 300;
        }

        body a {
            color: <?= $this->theme->variable('text-color-contrast', '#fff') ?>;
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
    <p><?= Yii::t('WebModule.pwa', 'Please check your internet connection and <a href="?">refresh</a> this page once you are online again!'); ?></p>
</div>
</body>
</html>
