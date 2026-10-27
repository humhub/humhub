<?php

use humhub\components\View;
use humhub\modules\admin\widgets\AuthenticationMenu;
use humhub\widgets\ContentCard;

/* @var View $this */
/* @var string $content */
?>
<?php ContentCard::begin([
    'title' => Yii::t('AdminModule.user', 'Settings'),
    'tabs' => AuthenticationMenu::class,
]) ?>
<?= $content ?>
<?php ContentCard::end() ?>
