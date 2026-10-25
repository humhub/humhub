<?php

use humhub\components\View;
use humhub\modules\friendship\widgets\ManageMenu;
use humhub\modules\user\models\User;
use humhub\widgets\ContentCard;
use humhub\widgets\PageHeader;

/* @var View $this */
/* @var User $user */
/* @var string $content */

$tabs = new ManageMenu(['user' => $user]);
?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('FriendshipModule.base', 'Friends'))->tabs($tabs) ?>
    <?php ContentCard::begin(['title' => $tabs->getActiveLabel() ?? Yii::t('FriendshipModule.base', 'Friends')]) ?>
        <?= $content ?>
    <?php ContentCard::end() ?>
</div>
