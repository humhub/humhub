<?php

/* @var $user \humhub\modules\user\models\User */
/* @var $url string */
/* @var $content \humhub\modules\content\models\Content */
/* @var $contentAddon \humhub\modules\content\interfaces\ContentProvider */
/* @var $contentContainer \humhub\modules\content\models\ContentContainer */
/* @var $createdAt string */

/* @var $message string */

use humhub\modules\space\models\Space;

?>

---

<?= $message ?>
<?php if ($contentContainer->polymorphicRelation instanceof Space) : ?>
    (<?= Yii::t('ActivityModule.base', 'via') ?> <?= $contentContainer->polymorphicRelation->displayName ?>)
<?php endif; ?>

<?php if ($url != '') : ?>
    <?= Yii::t('ActivityModule.base', 'See online:') ?> <?= urldecode($url) ?>
<?php endif; ?>
