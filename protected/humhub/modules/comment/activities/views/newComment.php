<?php

use yii\helpers\Html;

echo Yii::t('CommentModule.views_activities_CommentCreated', "%displayName% wrote a new comment ", [
    '%displayName%' => '<strong>' . Html::encode($originator->displayName) . '</strong>'
]);

echo ' "' . \humhub\widgets\RichText::widget(['text' => $source->message, 'minimal' => true, 'maxLength' => 100]) . '"';
?>