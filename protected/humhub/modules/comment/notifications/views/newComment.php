<?php

use yii\helpers\Html;

$commentedObject = $source->content->getPolymorphicRelation();
?>
<?php
echo Yii::t('CommentModule.views_notifications_newCommented', "%displayName% commented %contentTitle%.", array(
    '%displayName%' => '<strong>' . Html::encode($source->user->displayName) . '</strong>',
    '%contentTitle%' => $this->context->getContentInfo($commentedObject)
));
?>
<em>"<?php echo humhub\widgets\RichText::widget(['text' => $source->message, 'minimal' => true, 'maxLength' => 400]); ?>"</em>