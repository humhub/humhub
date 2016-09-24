<?php

use yii\helpers\Html;

echo Yii::t('CommentModule.views_activities_CommentCreated', "%displayName% wrote a new comment ", array(
    '%displayName%' => '<strong>' . Html::encode($originator->displayName) . '</strong>'
));
?>
<br />

<em>"<?= \humhub\widgets\RichText::widget(['text' => $source->message, 'minimal' => true]); ?>"</em>