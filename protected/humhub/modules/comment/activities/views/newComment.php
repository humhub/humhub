<?php

use yii\helpers\Html;
use humhub\modules\content\widgets\richtext\RichText;

/* @var $originator \humhub\modules\user\models\User */
/* @var $source \humhub\modules\comment\models\Comment */

echo Yii::t('CommentModule.views_activities_CommentCreated', "%displayName% wrote a new comment ", [
    '%displayName%' => '<strong>' . Html::encode($originator->displayName) . '</strong>'
]);

echo ' "' . RichText::preview($source->message,  100) . '"';
?>