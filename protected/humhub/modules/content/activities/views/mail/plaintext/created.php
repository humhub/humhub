<?php

use humhub\modules\content\widgets\richtext\converter\RichTextToPlainTextConverter;

/* @var $originator \humhub\modules\user\models\User */
/* @var $source \humhub\modules\content\interfaces\ContentOwner */

echo Yii::t('ContentModule.activities', '{displayName} created a new {contentTitle}.', [
    '{displayName}' => $originator->displayName,
    '{contentTitle}' => $source->getContentName()
]);
?>

"<?= RichTextToPlainTextConverter::process($source->getContentDescription(), [
    RichTextToPlainTextConverter::OPTION_CACHE_KEY => RichTextToPlainTextConverter::buildCacheKeyForContent($source)
]) ?>"
