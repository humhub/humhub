<?php

use humhub\helpers\Html;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\content\widgets\richtext\converter\RichTextToShortTextConverter;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\content\activities\ContentCreated;
use humhub\modules\activity\models\Activity;

/* @var $originator \humhub\modules\user\models\User */
/* @var $source ContentOwner */

echo Yii::t('ContentModule.activities', '{displayName} created a new {contentTitle}.', [
    '{displayName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
    '{contentTitle}' => '<strong>' . Html::encode($source->getContentName()) . '</strong>'
]);

$maxLength = 0;

// get corresponding mailPreviewLength if any
$activity = Activity::findOne(['object_id' => $source->id]);
if ($activity && ($activityBaseClass = $activity->getActivityBaseClass()) instanceof ContentCreated) {
    $maxLength = $activityBaseClass->mailPreviewLength;
}

?>
<br>

"<?= RichText::preview($source->getContentDescription(), $maxLength, [
    RichTextToShortTextConverter::OPTION_CACHE_KEY => RichTextToShortTextConverter::buildCacheKeyForContent($source)
]) ?>"
