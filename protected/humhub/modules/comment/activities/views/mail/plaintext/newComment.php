<?php

use humhub\modules\content\widgets\richtext\converter\RichTextToPlainTextConverter;

/* @var $originator \humhub\modules\user\models\User */
/* @var $source \humhub\modules\comment\models\Comment */

echo Yii::t('CommentModule.base', "{displayName} wrote a new comment ", [
    '{displayName}' => $originator->displayName
]);

?>

"<?= RichTextToPlainTextConverter::process($source->message, [
    RichTextToPlainTextConverter::OPTION_CACHE_KEY => RichTextToPlainTextConverter::buildCacheKeyForRecord($source)
]) ?>"
