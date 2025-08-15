<?php

use humhub\helpers\Html;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\content\widgets\richtext\converter\RichTextToShortTextConverter;
use humhub\modules\content\widgets\richtext\RichText;

/* @var $originator \humhub\modules\user\models\User */
/* @var $source ContentOwner */
/* @var $viewable \humhub\modules\content\activities\ContentCreated */

echo Yii::t('ContentModule.activities', '{displayName} created a new {contentTitle}.', [
    '{displayName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
    '{contentTitle}' => '<strong>' . Html::encode($source->getContentName()) . '</strong>'
]);
?>
<br>

"<?= RichText::preview($source->getContentDescription(), $viewable->mailPreviewLength, [
    RichTextToShortTextConverter::OPTION_CACHE_KEY => RichTextToShortTextConverter::buildCacheKeyForContent($source)
]) ?>"
