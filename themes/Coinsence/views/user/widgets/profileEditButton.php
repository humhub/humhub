<?php

use yii\helpers\Html;
use yii\helpers\Url;

print Html::a('<img src="'.Yii::$app->getModule('xcoin')->getAssetsUrl() . '/images/connect.svg'.'" alt="">'.Yii::t("UserModule.widgets_views_profileEditButton", "Edit account"), Url::toRoute('/user/account/edit'), ['class' => 'btn btn-info editProfile']);
