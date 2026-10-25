<?php

use humhub\components\View;
use humhub\modules\user\widgets\AccountProfileMenu;
use humhub\widgets\ContentCard;
use humhub\widgets\PageHeader;

/* @var View $this */
/* @var string $content */
/* @var string|null $description below the card title, given by the view */

$tabs = new AccountProfileMenu();
?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('UserModule.account', 'Profile'))->tabs($tabs) ?>
    <?php ContentCard::begin([
        'title' => $tabs->getActiveLabel() ?? Yii::t('UserModule.account', 'Profile'),
        'description' => $description ?? '',
    ]) ?>
        <?= $content ?>
    <?php ContentCard::end() ?>
</div>
