<?php

use humhub\modules\content\widgets\richtext\RichText;
use yii\helpers\Html;

/* @var $originator \humhub\modules\user\models\User */
/* @var $source \humhub\modules\content\interfaces\ContentOwner */

echo Yii::t('ContentModule.activities_views_created', '{displayName} created a new {contentTitle}.', [
    '{displayName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
    '{contentTitle}' => '<strong>' . Html::encode($source->getContentName()) . '</strong>'
]);
?>
<br />
<em>"<?= RichText::preview($source->getContentDescription()); ?>"</em>