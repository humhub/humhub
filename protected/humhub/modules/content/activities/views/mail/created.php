<?php

use humhub\modules\content\widgets\richtext\RichText;
use yii\helpers\Html;

/* @var $originator \humhub\modules\user\models\User */
/* @var $source \humhub\modules\content\interfaces\ContentOwner */

echo Yii::t('ContentModule.activities', '{displayName} created a new {contentTitle}.', [
    '{displayName}' => '<strong>' . Html::encode($originator->displayName) . '</strong>',
    '{contentTitle}' => '<strong>' . Html::encode($source->getContentName()) . '</strong>'
]);
?>
<br />
"<?= RichText::preview($source->getContentDescription()); ?>"
