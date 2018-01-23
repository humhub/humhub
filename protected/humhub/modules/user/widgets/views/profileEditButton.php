<?php

use yii\helpers\Html;
use yii\helpers\Url;

print Html::a(Yii::t('UserModule.widgets_views_profileEditButton', 'Edit account'), Url::toRoute('/user/account/edit'), ['class' => 'btn btn-primary edit-account']);
