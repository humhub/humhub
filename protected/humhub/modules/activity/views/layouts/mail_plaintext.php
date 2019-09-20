<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
 
use yii\helpers\Html;
?>

---

<?= $content; ?>
<?php if (isset($record->content->space) && $record->content->space !== null) : ?>
    (<?= strip_tags(Yii::t('ActivityModule.base', 'via')); ?> <?= Html::encode($record->content->space->name); ?>)
<?php endif; ?>
<?php if ($url != '') : ?>
    <?= strip_tags(Yii::t('ActivityModule.base', 'See online:')); ?> <?= urldecode($url); ?>
<?php endif; ?>
