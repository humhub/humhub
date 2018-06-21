<?php

use humhub\modules\content\widgets\richtext\RichText;
use yii\helpers\Html;

/* @var $originator \humhub\modules\user\models\User */
/* @var $source \humhub\modules\content\interfaces\ContentOwner */

echo strip_tags(Yii::t('ContentModule.activities_views_created', '{displayName} created a new {contentTitle}.', [
    '{displayName}' => Html::encode($originator->displayName),
    '{contentTitle}' => html_entity_decode(Html::encode($source->getContentName()))
]));
?>

"<?= strip_tags(RichText::preview($source->getContentDescription())); ?>"