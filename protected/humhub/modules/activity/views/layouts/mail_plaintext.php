<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $this \yii\web\View */
/* @var $space Space */
/* @var $url string */
/* @var $contentContainer ContentContainerActiveRecord */
/* @var $html string */
/* @var $text string */
/* @var $originator User */

use humhub\modules\content\components\ContentContainerActiveRecord;
?>

---

<?= $content ?>
<?php if (!empty($space)) : ?>
    (<?= strip_tags(Yii::t('ActivityModule.base', 'via')) ?> <?= $space->displayName ?>)
<?php endif; ?>

<?php if ($url != '') : ?>
    <?= strip_tags(Yii::t('ActivityModule.base', 'See online:')) ?> <?= urldecode($url) ?>
<?php endif; ?>
