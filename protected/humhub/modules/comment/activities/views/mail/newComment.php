<?php

use humhub\helpers\Html;
use humhub\modules\content\widgets\richtext\converter\RichTextToShortTextConverter;
use humhub\modules\content\widgets\richtext\RichText;

/* @var $originator \humhub\modules\user\models\User */
/* @var $source \humhub\modules\comment\models\Comment */

echo Yii::t('CommentModule.base', "{displayName} wrote a new comment ", [
    '{displayName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>'
]);
?>
<br>

"<?= RichText::preview($source->message, 0, [
    RichTextToShortTextConverter::OPTION_CACHE_KEY => RichTextToShortTextConverter::buildCacheKeyForRecord($source)
]) ?>"
