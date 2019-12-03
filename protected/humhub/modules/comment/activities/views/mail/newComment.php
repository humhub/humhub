<?php

use humhub\modules\content\widgets\richtext\RichText;
use yii\helpers\Html;

/* @var $originator \humhub\modules\user\models\User */
/* @var $source \humhub\modules\comment\models\Comment */

echo Yii::t('CommentModule.base', "{displayName} wrote a new comment ", [
    '{displayName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>'
]);
?>
<br>

<em>"<?= RichText::preview($source->message); ?>"</em>