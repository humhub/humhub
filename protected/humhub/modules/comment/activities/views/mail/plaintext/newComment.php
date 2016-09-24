<?php

use yii\helpers\Html;

echo strip_tags(Yii::t('CommentModule.views_activities_CommentCreated', "%displayName% wrote a new comment ", array(
    '%displayName%' => Html::encode($originator->displayName)
)));

?>

"<?= strip_tags(\humhub\widgets\RichText::widget(['text' => $source->message, 'minimal' => true])); ?>"