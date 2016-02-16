<?php

use yii\helpers\Html;

$commentedObject = $source->content->getPolymorphicRelation();
?>
<?php
echo strip_tags(Yii::t('CommentModule.views_notifications_newCommented', "%displayName% commented %contentTitle%.", array(
    '%displayName%' => Html::encode($source->user->displayName),
    '%contentTitle%' => $this->context->getContentInfo($commentedObject)
)));
?>

"<?php echo strip_tags(humhub\widgets\RichText::widget(['text' => $source->message, 'minimal' => true, 'maxLength' => 400])); ?>"