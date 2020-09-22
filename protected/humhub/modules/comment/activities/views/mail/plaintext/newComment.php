<?php

use humhub\modules\content\widgets\richtext\RichText;
use yii\helpers\Html;

/* @var $originator \humhub\modules\user\models\User */
/* @var $source \humhub\modules\comment\models\Comment */

echo strip_tags(Yii::t('CommentModule.base', "{displayName} wrote a new comment ", [
    '{displayName}' => Html::encode($originator->displayName)
]));

?>

"<?= strip_tags(RichText::preview($source->message)); ?>"