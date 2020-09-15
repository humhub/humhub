<?php

use yii\helpers\Html;

/* @var $space humhub\modules\space\models\Space */

?>
<div class="panel panel-default">
    <div class="container"><h1></h1><?= Yii::t('SpaceModule.profile', '<strong>About</strong> <b>{name}</b>', ['name' => $space->name]) ?></h1></div>
    <div class="container"><?= $space->description; ?></div>
    <br>
</div>
