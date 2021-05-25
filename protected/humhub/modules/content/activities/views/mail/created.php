<?php

use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\content\widgets\richtext\converter\RichTextToShortTextConverter;
use yii\helpers\Html;

/* @var $originator \humhub\modules\user\models\User */
/* @var $source \humhub\modules\content\interfaces\ContentOwner */

echo Yii::t('ContentModule.activities', '{displayName} created a new {contentTitle}.', [
    '{displayName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
    '{contentTitle}' => '<strong>' . Html::encode($source->getContentName()) . '</strong>'
]);
?>
<br>
"<?= RichText::preview($source->getContentDescription(), 0, [
    RichTextToShortTextConverter::OPTION_CACHE_KEY => RichTextToShortTextConverter::buildCacheKeyForContent($source)
]) ?>"
